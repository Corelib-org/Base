<?php
if(!defined('CACHE_MANAGER_REFERENCE_FILE')){
	define('CACHE_MANAGER_REFERENCE_FILE', 'var/cache/cachemanager.xml');
}
if(!defined('CACHE_MANAGER_DEFAULT_TTL')){
	define('CACHE_MANAGER_DEFAULT_TTL', 3600);
}

interface CacheableOutput {
	public function setCacheManagerOutput(CacheManagerOutput $cache);
}

interface CacheUpdateEvent {

}

class CacheManagerUpdate implements EventInstanceHandler,Observer {
	private $subject = null;
	private $cache = null;

	public function __construct(CacheManager $cache){
		$this->cache = $cache;
	}

	public function getInstanceType(){
		return 'CacheUpdateEvent';
	}
	public function register(ObserverSubject $subject){
		$this->subject = $subject;
	}
	public function update($update){
		$this->cache->update($update->getModel());
	}
}


class CacheManagerOutput {
	private $object;
	/**
	 * @var CacheManager;
	 */
	private $cache = null;

	private $type = null;

	private $reference_methods = array();

	public function __construct(CacheManager $cache, CacheableOutput $object, $type, $ttl=false){
		$this->cache = $cache;
		$this->object = $object;
		$this->type = $type;
		$this->object->setCacheManagerOutput($this);

		if($this->cache->getTTL() < $ttl){
			$this->cache->setTTL($ttl);
		}
	}

	public function addObjectReferenceMethod($method){
		$this->reference_methods[] = $method;
	}

	public function getCacheManagerOutput(CacheableOutput $object){
		return $this->cache->getCacheManagerOutput($object, $this->type, false);
	}

	public function getObject(){
		if($this->type == PAGE_OUTPUT_CACHE_STATIC && ($this->cache->getType() == PAGE_FACTORY_CACHE_STATIC || $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC)){
			return $this->object;
		}
	}

	public function getObjectReferenceMethods(){
		return $this->reference_methods;
	}
}

class CacheManager {
	private $type = PAGE_FACTORY_CACHE_DYNAMIC;
	private $filename = null;
	private $cached = null;
	private $ttl = null;
	private $data = null;
	private $data_updated = false;
	private $output = array();
	private $reference = null;

	private $content = array();
	private $settings = array();

	const TTL_FILE_SUFFIX = '.ttl';
	const HEADER_FILE_SUFFIX = '.headers';

	public function __construct($filename){
		$dir = dirname($filename);
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		$this->filename = realpath($dir).'/'.basename($filename);
		EventHandler::getInstance()->registerInstanceObserver(new CacheManagerUpdate($this));
	}

	public function setType($type=PAGE_FACTORY_CACHE_DYNAMIC){
		$this->type = $type;
	}

	public function getType(){
		return $this->type;
	}
	public function getCacheManagerOutput(CacheableOutput $output, $type, $ttl=false){
		array_push($this->output, new CacheManagerOutput($this, $output, $type, $ttl));
	}

	public function getFilename(){
		return $this->filename;
	}

	public function setData($data){
		$this->_updateReference();

		$this->data = $data;
		$this->data_updated = true;

	}

	public function getTTL(){
		if(is_null($this->ttl)){
			if($this->hasTTL()){
				$this->ttl = (int) file_get_contents($this->cache_file.self::TTL_FILE_SUFFIX);
			} else {
				return false;
			}
		}
	}

	public function read(){
		if($this->data_updated){
			$this->_write();
		}
		if(is_file($this->getFilename().self::HEADER_FILE_SUFFIX)){
			include($this->getFilename().self::HEADER_FILE_SUFFIX);
		}
		$last_modified = filemtime($this->getFilename());
		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T'), $last_modified);
		if(!$ttl = $this->getTTL()){
			$ttl = CACHE_MANAGER_DEFAULT_TTL;
		}
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $last_modified+$ttl));
		header('Cache-Control: private, no-store');
		header('Pragma:');
		if($this->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
			$output = new CacheManagerPageContainer($this->getFilename(), $this->content, $this->settings);
			return $output->draw();
		} else {
			return file_get_contents($this->getFilename());
		}
	}

	public function isCached(){
		if(is_null($this->cached) && !PAGE_FACTORY_CACHE_DEBUG){
			$this->cached = is_file($this->getFilename());
		} else if(PAGE_FACTORY_CACHE_DEBUG){
			$this->cached = false;
		}
		return $this->cached;
	}

	public function hasTTL(){
		return is_file($this->getFilename().self::TTL_FILE_SUFFIX);
	}

	public function update(CacheableOutput $output){
		$class = new ReflectionClass($output);
		if($class->hasMethod('getID')){
			$classname = $class->getMethod('getID')->class;
			if($id = $output->getID()){
				$this->_loadReferenceFile();
				$xpath = new DOMXPath($this->reference);
				if($pages = $xpath->evaluate('pages/page[object[@name = \''.$classname.'\' and @id=\''.$id.'\']]/@filename')){
					$i = 0;
					while($page = $pages->item($i++)){
						if(is_file($page->nodeValue)){
							unlink($page->nodeValue);
						}
						if(is_file($page->nodeValue.self::TTL_FILE_SUFFIX)){
							unlink($page->nodeValue.self::TTL_FILE_SUFFIX);
						}
						if(is_file($page->nodeValue.self::HEADER_FILE_SUFFIX)){
							unlink($page->nodeValue.self::HEADER_FILE_SUFFIX);
						}
					}
				}
			}
		}
	}

	public function addDynamicContent(Output $content){
		$this->content[get_class($content)][] = $content;
	}
	public function addDynamicSettings(Output $settings){
		$this->settings[get_class($settings)][] = $settings;
	}

	public function __destruct(){
		if($this->data_updated){
			$this->_write();
		}
	}

	private function _write(){
		if($ttl = $this->getTTL()){
			if(time() > (filemtime($this->getFilename()) + $ttl)){
				unlink($this->getFilename());
			}
			file_put_contents($this->getFilename().self::TTL_FILE_SUFFIX, $this->ttl);
		}

		$headers = '<?php'."\n";
		foreach(headers_list() as $header){
			if(preg_match('/^Content-/', $header, $match)){
				$headers .= 'header(\''.addcslashes($header, '\'').'\');'."\n";
			}
		}
		$headers .= '?>';
		file_put_contents($this->getFilename().self::HEADER_FILE_SUFFIX, $headers);


		file_put_contents($this->getFilename(), $this->data);
		if($this->type == PAGE_FACTORY_CACHE_DYNAMIC){
			chmod($this->getFilename(), 0777);
		}
		$this->data_updated = false;
	}

	private function _loadReferenceFile(){
		if(is_null($this->reference)){
			$this->reference = new DOMDocument('1.0', 'UTF-8');
			if(!is_file(CACHE_MANAGER_REFERENCE_FILE)){
				$this->reference->appendChild($this->reference->createElement('references'));
			} else {
				$this->reference->load(CACHE_MANAGER_REFERENCE_FILE);
			}
		}

	}

	private function _saveReferenceFile(){
		if(!is_null($this->reference)){
			$this->reference->save(CACHE_MANAGER_REFERENCE_FILE);
		}
	}

	private function _updateReference(){
		$this->_loadReferenceFile();
		$xpath = new DOMXPath($this->reference);
		if(!$pages = $xpath->evaluate('/references/pages')->item(0)){
			$pages = $this->reference->documentElement->appendChild($this->reference->createElement('pages'));
		}

		if(!$page = $xpath->evaluate('page[@filename = \''.$this->getFilename().'\']', $pages)->item(0)){
			$page = $pages->appendChild($this->reference->createElement('page'));
			$page->setAttribute('filename', $this->getFilename());
		}

		while(list(,$val) = each($this->output)){
			$this->_analyzeObject($val, $page, $xpath);
		}
		$this->_saveReferenceFile();
	}

	private function _analyzeObject(CacheManagerOutput $output, DOMElement $page, DOMXpath $xpath){
		if($object = $output->getObject()){
			$class = new ReflectionClass($object);
			if($class->hasMethod('getID')){
				$classname = $class->getMethod('getID')->class;
				if(!$xpath->evaluate('object[@name = \''.$classname.'\' and @id=\''.$object->getID().'\']', $page)->item(0)){
					$obj = $page->appendChild($page->ownerDocument->createElement('object'));
					$obj->setAttribute('name', $classname);
					$obj->setAttribute('id', $object->getID());
					foreach($output->getObjectReferenceMethods() as $method){
						$reference = $object->$method();
						if($reference instanceof CacheableOutput){
							$this->getCacheManagerOutput($reference);
						}
					}
				}
			}
		}
	}
}


class CacheManagerPageContainer {
	private $content = array();
	private $settings = array();

	private $file = null;

	public function __construct($file, array &$content, array &$settings){
		$this->content = $content;
		$this->settings = $settings;
		$this->file = $file;
	}

	public function each(Output $list, $code){
		$return = '';
		while($current = $list->each()){
			$return .= eval($code);
		}
		return $code;
	}

	public function draw(){
		return eval('include(\''.$this->file.'\');');
	}
}

/**
 * Parsetime calculator toolbox item.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package corelib
 * @subpackage Base
 */
class PageFactoryDeveloperToolbarItemCacheStatus extends PageFactoryDeveloperToolbarItem {
	/**
	 * @var CacheManager
	 */
	private $cache = null;

	public function __construct(CacheManager $cache){
		$this->cache = $cache;
	}

	public function getToolbarItem(){
		switch($this->cache->getType()){
			case PAGE_FACTORY_CACHE_DYNAMIC:
				$status[] = 'Dyanmic';
				break;
			case PAGE_FACTORY_CACHE_STATIC:
				$status[] = 'Static';
				break;
			case PAGE_FACTORY_CACHE_DISABLED:
				$status[] = 'Disabled';
				break;
		}
		if($this->cache->getTTL()){
			$status[] = 'TTL: '.$this->cache->getTTL();
		}
		if($this->cache->getType() !== PAGE_FACTORY_CACHE_DISABLED){
			$status[] = 'Cached: '.($this->cache->isCached() ? 'Yes' : '<span class="warning">No</span>');
		}

		return '<img src="corelib/resource/manager/images/page/icons/toolbar/cache.png" alt="parsetime" title="Cache status"/> '.implode(', ', $status);
	}

	public function getContent(){
		return false;
	}
}

?>