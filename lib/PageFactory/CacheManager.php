<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory Abstract Page Class
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.1.0 ($Id: PageBase.php 5168 2010-03-03 12:17:09Z wayland $)
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('CACHE_MANAGER_REFERENCE_FILE')){
	/**
	 * Cache manager reference file.
	 *
	 * @var string filename
	 */
	define('CACHE_MANAGER_REFERENCE_FILE', BASE_CACHE_DIRECTORY.'cachemanager.xml');
}
if(!defined('CACHE_MANAGER_DEFAULT_TTL')){
	/**
	 * Cache manager default Time-to-live
	 *
	 * @var integer ttl in seconds.
	 */
	define('CACHE_MANAGER_DEFAULT_TTL', 3600);
}


//*****************************************************************//
//****************** CacheableOutput interface ********************//
//*****************************************************************//
/**
 * Cachable output interface.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 */
interface CacheableOutput {


	//*****************************************************************//
	//************* CacheableOutput interface methods *****************//
	//*****************************************************************//
	/**
	 * Set cache manager output.
	 *
	 * If CacheableOutput is implimented on a {@link Output}.
	 * When it is called reference methods for external key
	 * objects can be set using {@link CacheManagerOutput::addObjectReferenceMethod()}.
	 *
	 * @param CacheManagerOutput $cache
	 * @return void
	 */
	public function setCacheManagerOutput(CacheManagerOutput $cache);
}


//*****************************************************************//
//****************** CacheableOutput interface ********************//
//*****************************************************************//
/**
 * Cachable output event interface.
 *
 * impliment this event on all your object on delete and on update
 * events in order to make PageFactory clear the cache for the object
 * when it is being updated.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 */
interface CacheUpdateEvent { }


//*****************************************************************//
//******************* CacheManagerUpdate class ********************//
//*****************************************************************//
/**
 * Cache manager update
 *
 * This event actions takes care of the the cache flush
 * actions. it is triggered by event instance {@link CacheUpdateEvent}
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class CacheManagerUpdate extends EventInstanceAction {


	//*****************************************************************//
	//************* CacheManagerUpdate class properties ***************//
	//*****************************************************************//
	/**
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;


	//*****************************************************************//
	//************** CacheManagerUpdate class methods *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param CacheManager $cache
	 * @return void
	 */
	public function __construct(CacheManager $cache){
		$this->cache = $cache;
	}

	/**
	 * @see EventAction::update($event)
	 */
	public function update(Event $event){
		$this->cache->update($event->getModel());
	}
}


//*****************************************************************//
//******************* CacheManagerOutput class ********************//
//*****************************************************************//
/**
 * Cache manager update
 *
 * This event actions takes care of the the cache flush
 * actions. it is triggered by event instance {@link CacheUpdateEvent}
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class CacheManagerOutput {


	//*****************************************************************//
	//************* CacheManagerOutput class properties ***************//
	//*****************************************************************//
	/**
	 * @var CacheableOutput
	 * @internal
	 */
	private $object;

	/**
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;

	/**
	 * @var cache type
	 * @internal
	 */
	private $type = null;

	/**
	 * @var array reference methods
	 * @internal
	 */
	private $reference_methods = array();


	//*****************************************************************//
	//*************** CacheManagerOutput class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param CacheManager $cache
	 * @param CacheableOutput $object
	 * @param integer $type cache type
	 * @param integer $ttl time-to-live in seconds
	 * @return void
	 */
	public function __construct(CacheManager $cache, CacheableOutput $object, $type, $ttl=false){
		$this->cache = $cache;
		$this->object = $object;
		$this->type = $type;
		$this->object->setCacheManagerOutput($this);

		if($this->cache->getTTL() < $ttl){
			$this->cache->setTTL($ttl);
		}
	}

	/**
	 * Add object reference method.
	 *
	 * When information about a record object is gathered
	 * is is possible to add methods for referencial purposes
	 * and make the cache clean other record objects as well
	 *
	 * @param string $method method name to get record object
	 * @return boolean true on success, else return false
	 */
	public function addObjectReferenceMethod($method){
		$this->reference_methods[] = $method;
		return true;
	}

	/**
	 * Get Cache manager output object.
	 *
	 * get a cache manager output object for a {@link CacheableOutput}.
	 * This is usually used by record list objects.
	 *
	 * @param CacheableOutput $object
	 * @return boolean true on success, else return false
	 */
	public function getCacheManagerOutput(CacheableOutput $object){
		return $this->cache->getCacheManagerOutput($object, $this->type, false);
	}

	/**
	 * Get record object.
	 *
	 * @return mixed object on success, else return false
	 */
	public function getObject(){
		if($this->type == PAGE_OUTPUT_CACHE_STATIC && ($this->cache->getType() == PAGE_FACTORY_CACHE_STATIC || $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC)){
			return $this->object;
		} else {
			return false;
		}
	}

	/**
	 * Get object reference methods.
	 *
	 * @return array list of reference methods
	 */
	public function getObjectReferenceMethods(){
		return $this->reference_methods;
	}
}


//*****************************************************************//
//*********************** CacheManager class **********************//
//*****************************************************************//
/**
 * Cache manager
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class CacheManager {


	//*****************************************************************//
	//**************** CacheManager class properties ******************//
	//*****************************************************************//
	/**
	 * @var integer cache type
	 * @internal
	 */
	private $type = PAGE_FACTORY_CACHE_DYNAMIC;

	/**
	 * Cache filename.
	 *
	 * @var string filename
	 * @internal
	 */
	private $filename = null;

	/**
	 * @var boolean if cached true, else false
	 * @internal
	 */
	private $cached = null;

	/**
	 * @var integer time to live
	 * @internal
	 */
	private $ttl = null;

	/**
	 * Cached data.
	 *
	 * @var string
	 * @internal
	 */
	private $data = null;

	/**
	 * @var boolean true if updated, else false
	 * @internal
	 */
	private $data_updated = false;

	/**
	 * @var array list Output objects
	 * @internal
	 */
	private $output = array();

	/**
	 * @var DOMDocument Reference document
	 * @internal
	 */
	private $reference = null;

	/**
	 * @var array page information
	 * @internal
	 */
	private $page = null;

	/**
	 * @var array list of content objects
	 * @internal
	 */
	private $content = array();

	/**
	 * @var array list of content settings objects
	 * @internal
	 */
	private $settings = array();


	//*****************************************************************//
	//***************** CacheManager class constants ******************//
	//*****************************************************************//
	/**
	 * @var string time to live files suffix
	 * @internal
	 */
	const TTL_FILE_SUFFIX = '.ttl';

	/**
	 * @var string http header file suffix
	 * @internal
	 */
	const HEADER_FILE_SUFFIX = '.headers';

	/**
	 * @var string page file suffix
	 * @internal
	 */
	const PAGE_FILE_SUFFIX = '.page';


	//*****************************************************************//
	//****************** CacheManager class methods *******************//
	//*****************************************************************//
	/**
	 * Create new instance
	 *
	 * @param string $filename cache target filename
	 * @return void
	 * @internal
	 */
	public function __construct($filename){
		$dir = dirname($filename);
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		$this->filename = realpath($dir).'/'.basename($filename);
		EventHandler::getInstance()->register(new CacheManagerUpdate($this), 'CacheUpdateEvent');
	}

	/**
	 * Set cache type.
	 *
	 * @param integer $type
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function setType($type=PAGE_FACTORY_CACHE_DYNAMIC){
		$this->type = $type;
		return true;
	}

	/**
	 * Set Page information.
	 *
	 * @param string $filename page file
	 * @param string $method
	 * @param string $engine
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function setPage($filename, $method, $engine){
		$this->page['file'] = $filename;
		$this->page['method'] = $method;
		$this->page['engine'] = $engine;
		return true;
	}

	/**
	 * Get cached page call informations.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function getPage(){
		if(is_null($this->page) && is_file($this->getFilename().self::PAGE_FILE_SUFFIX)){
			include_once($this->getFilename().self::PAGE_FILE_SUFFIX);
		} else if(is_null($this->page)){
			$this->page = false;
		}
		return true;
	}

	/**
	 * Get callback method name.
	 *
	 * @return mixed string callback, else return boolean false
	 * @internal
	 */
	public function getCallback(){
		if($this->getPage()){
			return $this->page['exec'];
		} else {
			return false;
		}
	}

	/**
	 * Get template engine.
	 *
	 * @return mixed string template engine, else return boolean false
	 * @internal
	 */
	public function getEngine(){
		if($this->getPage()){
			return $this->page['engine'];
		} else {
			return false;
		}
	}

	/**
	 * Get cache type.
	 *
	 * @return integer cache type
	 * @internal
	 */
	public function getType(){
		return $this->type;
	}

	/**
	 * Get cache manager output for cacheableoutput object.
	 *
	 * @param CacheableOutput $output
	 * @param integer $type cache type
	 * @param integer $ttl time to live in seconds
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function getCacheManagerOutput(CacheableOutput $output, $type, $ttl=false){
		array_push($this->output, new CacheManagerOutput($this, $output, $type, $ttl));
		return true;
	}

	/**
	 * Get cached filename.
	 *
	 * @return string filename
	 * @internal
	 */
	public function getFilename(){
		return $this->filename;
	}

	/**
	 * Set cached data.
	 *
	 * @param string $data
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function setData($data){
		$this->_updateReference();

		$this->data = $data;
		$this->data_updated = true;
		return true;
	}

	/**
	 * Get cached page time to live.
	 *
	 * @return mixed integer time to live, else return false
	 * @internal
	 */
	public function getTTL(){
		if(is_null($this->ttl)){
			if($this->hasTTL()){
				$this->ttl = (int) file_get_contents($this->cache_file.self::TTL_FILE_SUFFIX);
			} else {
				return false;
			}
		}
	}

	/**
	 * Read cached data and send correct headers.
	 *
	 * @return string cached page
	 * @internal
	 */
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

	/**
	 * Is page cached.
	 *
	 * @return boolean true if page is cached, else return false
	 * @internal
	 */
	public function isCached(){
		if(is_null($this->cached) && !PAGE_FACTORY_CACHE_DEBUG){
			$this->cached = is_file($this->getFilename());
		} else if(PAGE_FACTORY_CACHE_DEBUG){
			$this->cached = false;
		}
		return $this->cached;
	}

	/**
	 * Does the cached page have a time to live?
	 *
	 * @return boolean true if ttl exists, else return false
	 * @internal
	 */
	public function hasTTL(){
		return is_file($this->getFilename().self::TTL_FILE_SUFFIX);
	}

	/**
	 * Update cached files.
	 *
	 * When a record is updated clean up after
	 * the update and delete all pages with information
	 * about that record.
	 *
	 * @param CacheableOutput $output
	 * @return boolean true on success, else return false
	 * @internal
	 */
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
		return true;
	}

	/**
	 * Add dynamic content to cached page.
	 *
	 * @param Output $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function addDynamicContent(Output $content){
		$this->content[get_class($content)][] = $content;
		return true;
	}

	/**
	 * Add dynamic settings to cached page.
	 *
	 * @param Output $settings
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function addDynamicSettings(Output $settings){
		$this->settings[get_class($settings)][] = $settings;
		return true;
	}

	/**
	 * Destroy object.
	 *
	 * when object is destroyed, write all cache changes to disk.
	 *
	 * @return void
	 * @internal
	 */
	public function __destruct(){
		if($this->data_updated){
			$this->_write();
		}
	}

	/**
	 * Write cached to disk.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
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

		if(is_array($this->page)){
			file_put_contents($this->getFilename().self::PAGE_FILE_SUFFIX, '<?php include(\''.$this->page['file'].'\'); $this->page = array(\'page\' => \''.$this->page['file'].'\', \'exec\' => \''.$this->page['method'].'\', \'engine\' => \''.$this->page['engine'].'\'); ?>');
		}

		$this->data_updated = false;
		return true;
	}

	/**
	 * Load XML reference file.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
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

	/**
	 * Save XML reference file.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _saveReferenceFile(){
		if(!is_null($this->reference)){
			$this->reference->save(CACHE_MANAGER_REFERENCE_FILE);
		}
	}

	/**
	 * Update XML reference file.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
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

	/**
	 * Analyze cacheable object.
	 *
	 * @param CacheManagerOutput $output
	 * @param DOMElement $page
	 * @param DOMXpath $xpath
	 * @return boolean true on success, else return false
	 * @internal
	 */
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
							$this->getCacheManagerOutput($reference, $this->type);
						}
					}
				}
			}
		}
		return true;
	}
}


//*****************************************************************//
//*************** CacheManagerPageContainer class *****************//
//*****************************************************************//
/**
 * Cache manager page container
 *
 * This object is a pseudo class with resembles the {@link PageBase}
 * however it is not compatible with {@link PageBase} is is only used
 * by the {@link CacheManager} to render a cached page.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class CacheManagerPageContainer {


	//*****************************************************************//
	//********* CacheManagerPageContainer class properties ************//
	//*****************************************************************//
	/**
	 * Content list.
	 *
	 * @var array output content
	 * @internal
	 */
	private $content = array();

	/**
	 * Settings list
	 * @var array output settings
	 * @internal
	 */
	private $settings = array();

	/**
	 * Cached filename.
	 *
	 * @var string filename
	 * @internal
	 */
	private $file = null;


	//*****************************************************************//
	//*************** CacheManagerPageContainer class *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $file cached filename
	 * @param array $content Output content list
	 * @param array $settings Output settings list
	 * @return void
	 * @internal
	 */
	public function __construct($file, array &$content, array &$settings){
		$this->content = $content;
		$this->settings = $settings;
		$this->file = $file;
	}

	/**
	 *
	 * @param Output $list
	 * @param unknown_type $code
	 * @return unknown_type
	 *
	 * XXX Find out what this is used for
	 */
	public function each(Output $list, $code){
		trigger_errors('Methods used', E_USER_NOTICE);
		$return = '';
		while($current = $list->each()){
			$return .= eval($code);
		}
		return $code;
	}

	/**
	 * Draw page.
	 *
	 * @return string page content
	 * @internal
	 */
	public function draw(){
		return eval('include(\''.$this->file.'\');');
	}
}


//*********************************************************************//
//******* PageFactoryDeveloperToolbarItemCacheStatus class ************//
//*********************************************************************//
/**
 * Cache status developer toolbar item.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class PageFactoryDeveloperToolbarItemCacheStatus extends PageFactoryDeveloperToolbarItem {


	//*********************************************************************//
	//**** PageFactoryDeveloperToolbarItemCacheStatus class properties ****//
	//*********************************************************************//
	/**
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;


	//*********************************************************************//
	//**** PageFactoryDeveloperToolbarItemCacheStatus class methods *******//
	//*********************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param CacheManager $cache
	 * @return void
	 * @internal
	 */
	public function __construct(CacheManager $cache){
		$this->cache = $cache;
	}

	/**
	 * Get toolbar item.
	 *
	 * Return a icon symbolising the cache and the cache type and state.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 * @return string html
	 * @internal
	 */
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

		return '<img src="corelib/resource/manager/images/icons/toolbar/cache.png" alt="parsetime" title="Cache status"/> '.implode(', ', $status);
	}
}

?>