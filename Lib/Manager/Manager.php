<?php
if(!defined('MANAGER_DATADIR')){
	define('MANAGER_DATADIR', 'var/db/manager/');
}
if(!defined('MANAGER_DEVELOPER_MODE')){
	define('MANAGER_DEVELOPER_MODE', false);
}

abstract class CorelibManagerExtension implements Singleton {
	private $name = '';
	private $description = '';
	private $properties = array();
	/**
	 * @var DOMXPath
	 */
	private $xpath = null;
	
	final public function setName($name){
		$this->name = $name;
		return true;
	}
	final public function setDescription($description){
		$this->description = $description;
		return true;
	}
	final public function getName(){
		return $this->name;
	}
	final public function getDescription(){
		return $this->description;
	}
	
	public function loaded(){
		
	}
	
	public function getPropertyXML($property){
		if(isset($this->properties[$property])){
			return $this->properties[$property];
		} else {
			return false;
		}
	}

	public function addBaseProperty(DOMElement $property){
		$this->properties[$property->nodeName] = $property;
	}
	public function addProperty(DOMElement $property){
		if(is_null($this->xpath)){
			$this->xpath = new DOMXPath($property->ownerDocument);
		}
		if(isset($this->properties[$property->nodeName]) && $this->properties[$property->nodeName]->getAttribute('locked') != 'true'){
			$this->_mergeNodes($this->properties[$property->nodeName], $property);
		}
	}
	private function _mergeNodes(DOMElement $DOMTarget, DOMElement $DOMSource){
		for ($i = 0; $item = $DOMSource->childNodes->item($i); $i++){
			if($item->nodeType != XML_TEXT_NODE && $item->getAttribute('id')){;
				$list = $this->xpath->query('child::*[@id = \''.$item->getAttribute('id').'\']', $DOMTarget);
				if($list->length > 0){
					$this->_mergeNodes($list->item(0), $item);
				} else {
					$DOMTarget->appendChild($item->cloneNode(true));	
				}
			} else {
				$DOMTarget->appendChild($item->cloneNode(true));
			}
		}
		return true;
	}
}


class Manager implements Singleton {
	private static $instance = null;

	const EXTENSIONS_FILE = 'extensions.xml';
	// const EXTENSIONS_DATA_FILE = 'extensions.dat';

	protected $extension_dirs = array(CORELIB, 
	                                  'var/');
	/**
	 * @var DOMDocument
	 */
	private $extensions = null;
	/**
	 * @var array
	 */
	private $extensions_data = array();

	protected function __construct(){
		if(!is_dir(MANAGER_DATADIR)){
			mkdir(MANAGER_DATADIR);
		}
		try {
			if(!is_writeable(MANAGER_DATADIR)){
				throw new BaseException(MANAGER_DATADIR.' is read-only');
			}
		} catch (BaseException $e){
			echo $e;
			exit;
		}
		if(!is_file(MANAGER_DATADIR.self::EXTENSIONS_FILE) || MANAGER_DEVELOPER_MODE){
			$this->_reloadManagerExtensions();
		} else { 
			$this->_loadExtensionsXML();
		}
		// if(!is_file(MANAGER_DATADIR.self::EXTENSIONS_DATA_FILE) || MANAGER_DEVELOPER_MODE){
			$this->_reloadManagerExtensionsData();
		// }
		

	}

	/**
	 *	@return Manager
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	public function addExtensionDir($dir){
		$this->extension_dirs[] = $dir;
	}

	public function setupPageRegistry(&$pages){
		$xpath = new DOMXPath($this->extensions);
		$pagelist = $xpath->query('//extensions/extension/pages/child::*');
		for ($i = 0; $page = $pagelist->item($i); $i++){
			$p = array();
			try {
				$file = $page->getElementsByTagName('file');
				if($file->length > 0){
					// eval('$p[\'page\'] = \''.preg_replace('/\{([A-Za-z_-]+)\}/', '\'.\\1.\'', $file->item(0)->nodeValue).'\';');
					$p['page'] = Manager::parseConstantTags($file->item(0)->nodeValue);
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
			$expr = $page->getElementsByTagName('expr');
			if($page->getAttribute('type') && $expr->length > 0){
				$p['type'] = $page->getAttribute('type');
				$p['expr'] = $expr->item(0)->nodeValue;
			}
			$exec = $page->getElementsByTagName('exec');
			if($exec->length > 0){
				$p['exec'] = $exec->item(0)->nodeValue;
			}
			
			$url = $page->getElementsByTagName('url');
			if($url->length > 0){
				$rurl = $url->item(0)->nodeValue;
			}
			if(isset($rurl)){
				$pages[$rurl] = $p;
			} else {
				$pages[] = $p;
			}
			unset($rurl);
		}
		return true;
	}
	
	public function getResource($handle, $resource){
		try {			
			StrictTypes::isString($handle);
			StrictTypes::isString($resource);
			$config = ManagerConfig::getInstance();
			if(!$dir = $config->getResourceDir($handle)){
				return false;
			} else if(!is_dir($dir)){
				throw new BaseException('No Such file or directory: '.$dir);
			}
			$filename = $dir.'/'.$resource;
			$filename = str_replace('../', '/', $filename);
			while(strstr($filename, '//')){
				$filename = str_replace('//', '/', $filename);
			}
			if(!is_file($filename)){
				return false;
			}
		} catch (BaseException $e){
			echo $e;
		}
		return $filename;
	}	
	
	public function getExtensionsXML(){
		return $this->extensions->documentElement;
	}
	
	static public function parseConstantTags($string){
		eval('$string = \''.preg_replace('/\{([A-Za-z_-]+)\}/', '\'.\\1.\'', addcslashes($string, '\'')).'\';');
		return $string;
	}	
		
	
	private function _reloadManagerExtensionsData(){
		$this->_loadExtensionsXML();
		$event = EventHandler::getInstance();
		$xpath = new DOMXPath($this->extensions);
		$properties = $this->extensions->getElementsByTagName('extension');
		for ($i = 0; $item = $properties->item($i); $i++){
			$data = array();
			if($setup = $item->getElementsByTagName('setup')->item(0)){
				try {
					$handler = $setup->getElementsByTagName('handler');
					if($handler->length > 0){
						eval('$handler = '.$handler->item(0)->nodeValue.'::getInstance();');
					} else {
						throw new BaseException('Invalid corelib extension '.$item->getAttribute('id').', no handler defined!', E_USER_ERROR);
					}
				} catch (BaseException $e){
					echo $e;
					exit;
				}
				
				for ($p = 0; $prop = $setup->childNodes->item($p); $p++){
					switch ($prop->nodeName){
						case 'name':
							$handler->setName($prop->nodeValue);
							break;
						case 'description':
							$handler->setDescription($prop->nodeValue);
							break;
						default:
							$event->triggerEvent(new ManagerUnknownSetupProperty($handler, $prop));
					}
				}

				$props = $xpath->query('//extensions/extension[@id = \''.$item->getAttribute('id').'\']/props/child::*');
				for ($p = 0; $prop = $props->item($p); $p++){
					$handler->addBaseProperty($prop);
				}
				$xdata = $xpath->query('//extensions/extension/extendprops[@id = \''.$item->getAttribute('id').'\']/child::*');
				for ($p = 0; $xitem = $xdata->item($p); $p++){
					$handler->addProperty($xitem);
				}
				$handler->loaded();
			}
		}
	}
	
	private function _reloadManagerExtensions(){
		$this->extensions = new DOMDocument('1.0', 'UTF-8');
		$this->extensions->appendChild($this->extensions->createElement('extensions'));
		while (list(,$val) = each($this->extension_dirs)) {
			$this->_searchDir($val);
		}
		reset($this->extension_dirs);
		$this->extensions->save(MANAGER_DATADIR.self::EXTENSIONS_FILE);
	}
	private function _loadExtensionsXML(){
		if(!$this->extensions instanceof DOMDocument){
			$this->extensions = new DOMDocument('1.0', 'UTF-8');
			$this->extensions->load(MANAGER_DATADIR.self::EXTENSIONS_FILE);
		}
	}
	protected function _searchDir($dir){
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if(preg_match('/\.cxd$/', $entry)){
				$this->_loadExtension($dir.'/'.$entry);
			} else if(is_dir($dir.'/'.$entry) && $entry != '.' && $entry != '..'){
				$this->_searchDir($dir.'/'.$entry);
			} else if($entry != '.' && $entry != '..'){
				$event = EventHandler::getInstance();
				$event->triggerEvent(new ManagerFileSearch($dir.'/'.$entry));
			}
		}
	}
	protected function _loadExtension($file){
		$extension = array();

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load($file);

		if($dom->getElementsByTagName('setup')->length > 0){
			$properties = $dom->getElementsByTagName('extendprops');
			for ($i = 0; $item = $properties->item($i); $i++){
				$extend = $item->getAttribute('id');
				if(empty($extend)){
					$item->setAttribute('id', $dom->documentElement->getAttribute('id'));
				}
			}
			$dom = $this->extensions->importNode($dom->documentElement, true);
			$this->extensions->documentElement->appendChild($dom);
		}
	}
}

class ManagerFileSearch implements Event {
	private $filename;

	public function __construct($filename){
		try {
			StrictTypes::isString($filename);
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		$this->filename = $filename;
	}
	public function getFilename(){
		return $this->filename;
	}
}

class ManagerUnknownSetupProperty implements Event {
	/**
	 * @var DOMNode
	 */
	private $property;
	/**
	 * @var CorelibManagerExtension
	 */
	private $handler;

	public function __construct(CorelibManagerExtension $handler, DOMNode $property){
		$this->property = $property;
		$this->handler = $handler;
	}
	/**
	 * @return DOMNode
	 */
	public function getProperty(){
		return $this->property;
	}
	/**
	 * @return CorelibManagerExtension
	 */
	public function getHandler(){
		return $this->handler;
	}
	/**
	 * @return string
	 */
	public function getPropertyName(){
		return $this->property->nodeName;
	}
}


abstract class ManagerPage extends PageBase {
	/**
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $xsl = null;

	final public function __construct(){
		define('DOMXSL_TEMPLATE_XSL_PATH', CORELIB);
		$this->xsl = new PageFactoryDOMXSLTemplate('Base/Share/Resources/XSLT/core.xsl');
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/layout.xsl');
		$this->addTemplateDefinition($this->xsl);
	}
	
	protected function _selectMenuItem($tab, $item){
/*		$xml = new DOMDocument('1.0', 'UTF-8');
		$select = $xml->createElement('menuselect');
		$select->setAttribnute
		return true; */
	}
}
?>