<?php
if(!defined('MANAGER_DATADIR')){
	define('MANAGER_DATADIR', 'var/cache/manager/');
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
	protected $xpath = null;
	
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
	
	public function install(){
		
	}
	
	public function getPropertyXML($property){
		if(isset($this->properties[$property])){
			return $this->properties[$property];
		} else {
			return false;
		}
	}
	
	public function getPropertyOutput($property){
		$output = new GenericOutput();
		if($xml = $this->getPropertyXML($property)){
			$output->setXML($xml);
			return $output;
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
	protected function _mergeNodes(DOMElement $DOMTarget, DOMElement $DOMSource){
		$this->_mergeAttributes($DOMTarget, $DOMSource);
		for ($i = 0; $item = $DOMSource->childNodes->item($i); $i++){
			if($item instanceof DOMElement && $item->getAttribute('id')){;
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
	
	private function _mergeAttributes(DOMElement $DOMTarget, DOMElement $DOMSource){
		for ($ia = 0; $attribute = $DOMSource->attributes->item($ia); $ia++){
			if(!$DOMTarget->getAttribute($attribute->nodeName) || $DOMSource->getAttribute('controller') == 'true'){
				$DOMTarget->setAttribute($attribute->nodeName, $attribute->nodeValue);
			}
		}		
	}
}

class UnknownCorelibManagerExtension extends CorelibManagerExtension {
	private static $instance = null;
	
	
	/**
	 *	@return UnknownCorelibManagerExtension
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new UnknownCorelibManagerExtension();
		}
		return self::$instance;
	}	
}


class Manager implements Singleton {
	private static $instance = null;

	const EXTENSIONS_FILE = 'extensions.xml';

	protected $extension_dirs = array(CORELIB, 
	                                  'var/',
	                                  'lib/');
	/**
	 * @var DOMDocument
	 */
	private $extensions = null;
	
	private $datadir = MANAGER_DATADIR;
	private $extension_file = '';
	
	/**
	 * @var array
	 */
	private $extensions_data = array();

	protected function __construct($datadir=null){
		if(!is_null($datadir)){
			$this->datadir = $datadir;	
		}
		
		if(!is_dir($this->datadir)){
			mkdir($this->datadir, 0777, true);
			@chmod($this->datadir, 0777);
		}
		try {
			if(!is_writeable($this->datadir)){
				throw new BaseException($this->datadir.' is read-only');
			}
		} catch (BaseException $e){
			echo $e;
			exit;
		}
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

	public function init(){
		$this->extension_file = $this->datadir.self::EXTENSIONS_FILE;
		if(!is_file($this->extension_file) || MANAGER_DEVELOPER_MODE){
			$this->_reloadManagerExtensions();
		} else { 
			$this->_reloadManagerExtensionsData();
		}
	}
	
	public function addExtensionDir($dir){
		$this->extension_dirs[] = $dir;
	}

	public function setupPageRegistry(&$pages){
		$xpath = new DOMXPath($this->extensions);
		$pagelist = $xpath->query('//extensions/extension/pages/'.strtolower($_SERVER['REQUEST_METHOD']).'/child::*');
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
	
	public function getDatadir(){
		return $this->datadir;
	}
	
	public function getExtensionsXML(){
		return $this->extensions->documentElement;
	}
	
	static public function parseConstantTags($string){
		eval('$string = \''.preg_replace('/\{([A-Za-z_-]+)\}/', '\'.\\1.\'', addcslashes($string, '\'')).'\';');
		return $string;
	}	
		
	
	private function _reloadManagerExtensionsData($install = false){
		$this->_loadExtensionsXML();
		$event = EventHandler::getInstance();
		$xpath = new DOMXPath($this->extensions);
		$properties = $this->extensions->getElementsByTagName('extension');
		for ($i = 0; $item = $properties->item($i); $i++){
			if($setup = $item->getElementsByTagName('setup')->item(0)){
				
				$handler = $setup->getElementsByTagName('handler');
				if($handler->length > 0){
					eval('$handler = '.$handler->item(0)->nodeValue.'::getInstance();');
					for ($p = 0; $prop = $setup->childNodes->item($p); $p++){
						switch ($prop->nodeName){
							case 'name':
								$handler->setName($prop->nodeValue);
								break;
							case 'description':
								$handler->setDescription($prop->nodeValue);
								break;
							default:
								if($prop->nodeType != XML_TEXT_NODE){
									$event->triggerEvent(new ManagerUnknownSetupProperty($handler, $prop));
								}
								break;
						}
					}
				} else {
					for ($p = 0; $prop = $setup->childNodes->item($p); $p++){
						if($prop->nodeType != XML_TEXT_NODE){
							$event->triggerEvent(new ManagerUnknownSetupProperty(UnknownCorelibManagerExtension::getInstance(), $prop));
						}
					}
					$handler = null;
//					throw new BaseException('Invalid corelib extension '.$item->getAttribute('id').', no handler defined!', E_USER_ERROR);
				}
				
				$this->extensions_data[] = array('handler' => $handler, 'node'=>$item);
			}
		}
		

		foreach ($this->extensions_data as $extension){
			if($extension['handler'] instanceof CorelibManagerExtension){
				$props = $xpath->query('//extensions/extension[@id = \''.$extension['node']->getAttribute('id').'\' and @enabled = \'true\']/props/child::*');
				for ($p = 0; $prop = $props->item($p); $p++){
					$extension['handler']->addBaseProperty($prop);
				}
		
				$xdata = $xpath->query('//extensions/extension[@enabled = \'true\']/extendprops[@id = \''.$extension['node']->getAttribute('id').'\']/child::*');
				for ($p = 0; $xitem = $xdata->item($p); $p++){
					$extension['handler']->addProperty($xitem);
				}
	
				$extension['handler']->loaded();
				if($install){
					$extension['handler']->install();
				}
			}
		}
	}
	
	protected function _reloadManagerExtensions(){
		$this->extensions = new DOMDocument('1.0', 'UTF-8');
		$this->extensions->appendChild($this->extensions->createElement('extensions'));
		foreach ($this->extension_dirs as $val){
			$this->_searchDir($val);
		}
		reset($this->extension_dirs);
		@chmod($this->extension_file, 0666);
		
		if(!is_file($this->extension_file) || MANAGER_DEVELOPER_MODE){
			$this->extensions->save($this->extension_file);
		} 
				
		$this->_reloadManagerExtensionsData(true);
	}
	private function _loadExtensionsXML(){
		if(!$this->extensions instanceof DOMDocument){
			$this->extensions = new DOMDocument('1.0', 'UTF-8');
			$this->extensions->load($this->extension_file);
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
		return $dom;
	}
	
	private function __clone(){
			
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
	protected $post = null;

	final public function __construct(){
		if(!defined('CORELIB_MANAGER_USERNAME')){
			define('CORELIB_MANAGER_USERNAME', 'admin');
		}
		if(!defined('CORELIB_MANAGER_PASSWORD')){
			define('CORELIB_MANAGER_PASSWORD', sha1(rand(100000, 900000)));
			$password_error = true;
		}
		
		if(@$_SERVER['PHP_AUTH_USER'] !== CORELIB_MANAGER_USERNAME || @$_SERVER['PHP_AUTH_PW'] !== CORELIB_MANAGER_PASSWORD){
		    header('WWW-Authenticate: Basic realm="Corelib v'.CORELIB_BASE_VERSION.'"');
		    header('HTTP/1.0 401 Unauthorized');
		    echo '<h1>Access Denied</h1>';
		    if(isset($password_error) && BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
		    	echo '<p>Notice: Constant <b>CORELIB_MANAGER_PASSWORD</b> is not defined</p>';
		    	echo '<p>Before you can log in this constant must be defined.</p>';
		    }
		    exit;
		}
		
		define('DOMXSL_TEMPLATE_XSL_PATH', CORELIB);
		$this->xsl = new PageFactoryDOMXSLTemplate('Base/Share/Resources/XSLT/core.xsl');
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/layout.xsl');
		$this->addTemplateDefinition($this->xsl);
		
		$this->post = new PageFactoryPostTemplate();
		$this->addTemplateDefinition($this->post);
	}
	
	protected function _selectMenuItem($tab, $item){
/*		$xml = new DOMDocument('1.0', 'UTF-8');
		$select = $xml->createElement('menuselect');
		$select->setAttribnute
		return true; */
	}
}


?>