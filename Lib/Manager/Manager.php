<?php
if(!defined('MANAGER_DATATIR')){
	define('MANAGER_DATADIR', 'var/db/manager/');
}
if(!defined('MANAGER_DEVELOPER_MODE')){
	define('MANAGER_DEVELOPER_MODE', false);
}

abstract class CorelibManagerConfig implements Singleton {
	abstract public function addProperties(DOMElement $properties);
}

class ManagerConfig extends CorelibManagerConfig {
	private static $instance = null;
	/**
	 *	@return ManagerConfig
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new ManagerConfig();
		}
		return self::$instance;
	}

	public function addProperties(DOMElement $properties){
		var_dump($properties);
	}
}
class ResourceManager extends CorelibManagerConfig {
	private static $instance = null;
	/**
	 *	@return ResourceManager
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new ResourceManager();
		}
		return self::$instance;
	}

	public function addProperties(DOMElement $properties){
		var_dump($properties);
	}
}

class Manager implements Singleton,Output {
	private static $instance = null;

	const EXTENSIONS_FILE = 'extensions.xml';
	const EXTENSIONS_DATA_FILE = 'extensions.dat';

	private $extension_dirs = array(CORELIB);
	/**
	 * @var DOMDocument
	 */
	private $extensions = null;
	/**
	 * @var array
	 */
	private $extensions_data = array();

	private function __construct(){
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
		}
		if(!is_file(MANAGER_DATADIR.self::EXTENSIONS_DATA_FILE) || MANAGER_DEVELOPER_MODE){
			$this->_reloadManagerExtensionsData();
		}
		header('Content-Type: text/xml');
		echo ($this->extensions->saveXML());

		exit;
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


	public function getXML(DOMDocument $xml){
		$this->_loadExtensionsXML();
		return $xml->importNode($this->extensions->documentElement);
	}
	public function &getArray(){

	}
	private function _reloadManagerExtensionsData(){
		echo '<pre>';
		$this->_loadExtensionsXML();
		$xpath = new DOMXPath($this->extensions);
		$properties = $this->extensions->getElementsByTagName('extension');
		for ($i = 0; $item = $properties->item($i); $i++){
			if($setup = $item->getElementsByTagName('setup')->item(0)){
				echo htmlentities($this->extensions->saveXML($item))."\n\n";
				$name = $setup->getElementsByTagName('name');
				if($name->length > 0){
					$data['name'] = $name->item(0)->nodeValue;
				}
				$description = $setup->getElementsByTagName('description');
				if($description->length > 0){
					$data['description'] = $description->item(0)->nodeValue;
				}
				$classes = $setup->getElementsByTagName('class');
				for ($c = 0; $class = $classes->item($c); $c++){
					switch ($class->getAttribute('type')){
						case 'handler':
							eval('$data[\'handler\'] = '.$class->nodeValue.'::getInstance();');
							break;
						case 'autoload':
							$data['autoload'][] = $class->nodeValue;
							break;
					}
				}
				$xdata = $xpath->query('//extensions/extension/properties[@extend = \''.$item->getAttribute('id').'\']');

				for ($p = 0; $xitem = $xdata->item($p); $p++){
					if(isset($data['handler'])){
						$data['handler']->addProperties($xitem);
					}
				}
			}
		}
		exit;
	}
	private function _reloadManagerExtensions(){
		$this->extensions = new DOMDocument('1.0', 'UTF-8');
		$this->extensions->appendChild($this->extensions->createElement('extensions'));
		while (list(,$val) = each($this->extension_dirs)) {
			$this->_searchDir($val);
		}
		reset($this->extension_dirs);
	}
	private function _loadExtensionsXML(){
		if(!$this->extensions instanceof DOMDocument){
			$this->extensions = new DOMDocument('1.0', 'UTF-8');
			$this->extensions->load(MANAGER_DATADIR.self::EXTENSIONS_FILE);
		}
	}
	private function _searchDir($dir){
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if(preg_match('/\.mext$/', $entry)){
				$this->_loadExtension($dir.'/'.$entry);
			} else if(is_dir($dir.'/'.$entry) && $entry != '.' && $entry != '..'){
				$this->_searchDir($dir.'/'.$entry);
			} else if($entry != '.' && $entry != '..'){
				$event = EventHandler::getInstance();
				$event->triggerEvent(new ManagerFileSearch($dir.'/'.$entry));
			}
		}
	}
	private function _loadExtension($file){
		$extension = array();

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load($file);

		if($dom->getElementsByTagName('setup')->length > 0){
			$properties = $dom->getElementsByTagName('properties');
			for ($i = 0; $item = $properties->item($i); $i++){
				$extend = $item->getAttribute('extend');
				if(empty($extend)){
					$item->setAttribute('extend', $dom->documentElement->getAttribute('id'));
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

abstract class ManagerPage extends Page {
	/**
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $xsl = null;

	final public function __construct(){
		define('DOMXSL_TEMPLATE_XSL_PATH', CORELIB);
		$this->xsl = new PageFactoryDOMXSLTemplate('Base/Share/Resources/XSLT/core.xsl');
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/layout.xsl');
		$this->addTemplateDefinition($this->xsl);

		$manager = Manager::getInstance();
		$this->addSettings($manager);
	}
}
?>