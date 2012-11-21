<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager.
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Interfaces.php 5218 2010-03-16 13:07:41Z wayland $)
 * @internal
 */


//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('MANAGER_DATADIR')){
	/**
	 * Manager datadir.
	 *
	 * Directory for where the manager should store
	 * it's runtime files.
	 *
	 * @var string directory
	 */
	define('MANAGER_DATADIR', BASE_CACHE_DIRECTORY.'manager/');
}

if(!defined('MANAGER_DEVELOPER_MODE')){
	/**
	 * Enable or disable manager developer mode.
	 *
	 * Enabling developer mode will cause the manager
	 * not to cache .cxd files and heavily decrease performance
	 * how ever when writing .cxd files this feature can come in
	 * handy.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('MANAGER_DEVELOPER_MODE', false);
}

//*****************************************************************//
//**************** CorelibManagerExtension class ******************//
//*****************************************************************//
/**
 * Corelib manager extension base class.
 *
 * In order to implement a new config handler for a extension.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 */
abstract class CorelibManagerExtension implements Singleton {


	//*****************************************************************//
	//*********** CorelibManagerExtension class properties ************//
	//*****************************************************************//
	/**
	 * @var string extension name
	 * @internal
	 */
	private $name = '';

	/**
	 * @var string extension description
	 * @internal
	 */
	private $description = '';

	/**
	 * @var array list of properties
	 * @internal
	 */
	private $properties = array();

	/**
	 * @var DOMXPath object instance
	 */
	protected $xpath = null;


	//*****************************************************************//
	//************ CorelibManagerExtension class methods **************//
	//*****************************************************************//
	/**
	 * Set extension name.
	 *
	 * @param string $name extension name
	 * @return boolean true on success, else return false
	 * @internal
	 */
	final public function setName($name){
		$this->name = $name;
		return true;
	}

	/**
	 * Set extension id.
	 *
	 * @param string $name extension id
	 * @return boolean true on success, else return false
	 * @internal
	 */
	final public function setID($id){
		$this->id = $id;
		return true;
	}

	/**
	 * Set extension manager.
	 *
	 * @param Manager $manager
	 * @return boolean true on success, else return false
	 * @internal
	 */
	final public function setManager(Manager $manager){
		$this->manager = $manager;
		return true;
	}

	/**
	 * Set extension description.
	 *
	 * @param string $description
	 * @return boolean true  on success, else return false
	 * @internal
	 */
	final public function setDescription($description){
		$this->description = $description;
		return true;
	}

	/**
	 * Get extension name.
	 *
	 * @return string extension name
	 */
	final public function getName(){
		return $this->name;
	}

	/**
	 * Get extension ID.
	 *
	 * @return string extension id
	 */
	final public function getID(){
		return $this->id;
	}
	/**
	 * Get extension description.
	 *
	 * @return string extension description
	 */
	final public function getDescription(){
		return $this->description;
	}

	/**
	 * Extension loaded.
	 *
	 * This method es executed when a extension is being loaded.
	 * overwrite it to implement you own action when your extension
	 * is beying loaded. a extension is loaded every time the manager
	 * is invoked. if you want to execute a action when the extension
	 * is installed take a look at {@link CorelibManagerExtension::install()}
	 *
	 * @return void
	 * @see CorelibManagerExtension::install()
	 */
	public function loaded(){

	}

	/**
	 * Extension install.
	 *
	 * This method es executed when a extension is being installed.
	 * overwrite it to implement you own action when your extension
	 * is beying installed.
	 *
	 * @return void
	 * @see CorelibManagerExtension::loaded()
	 */
	public function install(){

	}

	/**
	 * Enable extension.
	 *
	 * @return boolean true on success, else return false
	 * @see Manager::enableExtension()
	 */
	public function enable(){
		return $this->manager->enableExtension($this);
	}

	/**
	 * Disable extension.
	 *
	 * @return boolean true on success, else return false
	 * @see Manager::disableExtenstion()
	 */
	public function disable(){
		return $this->manager->disableExtension($this);
	}

	/**
	 * Get property XML.
	 *
	 * Get DOMelement for property.
	 *
	 * @param string $property property name
	 * @return DOMElement if property exists, else return false
	 */
	public function getPropertyXML($property){
		if(isset($this->properties[$property])){
			return $this->properties[$property];
		} else {
			return false;
		}
	}

	/**
	 * Get property output.
	 *
	 * Get {@link Output} for property name
	 *
	 * @param string $property property name
	 * @return XMLOutput
	 */
	public function getPropertyOutput($property){
		$output = new XMLOutput();
		if($xml = $this->getPropertyXML($property)){
			$output->setXML($xml);
			return $output;
		} else {
			return false;
		}
	}

	/**
	 * Add base property.
	 *
	 * add a base property from the extensions own
	 * property list.
	 *
	 * @param DOMElement $property
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function addBaseProperty(DOMElement $property){
		$this->properties[$property->nodeName] = $property;
		return true;
	}

	/**
	 * Add property from extendprop.
	 *
	 * Add and merge a property from another extension,
	 * which has a extendprops element for the extension.
	 *
	 * @param DOMElement $property
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function addProperty(DOMElement $property){
		if(is_null($this->xpath)){
			$this->xpath = new DOMXPath($property->ownerDocument);
		}
		if(isset($this->properties[$property->nodeName]) && $this->properties[$property->nodeName]->getAttribute('locked') != 'true'){
			$this->_mergeNodes($this->properties[$property->nodeName], $property);
		}
		return true;
	}

	/**
	 * Merge to DOMElements together
	 *
	 * merge the attributes and elements together using the
	 * target as master.
	 *
	 * @param DOMElement $DOMTarget merge elements into target
	 * @param DOMElement $DOMSource get elements from source
	 * @return true on success, else return false
	 * @internal
	 */
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

	/**
	 * Merge element attributes.
	 *
	 * @param DOMElement $DOMTarget merge elements into target
	 * @param DOMElement $DOMSource get elements from source
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _mergeAttributes(DOMElement $DOMTarget, DOMElement $DOMSource){
		for ($ia = 0; $attribute = $DOMSource->attributes->item($ia); $ia++){
			if(!$DOMTarget->getAttribute($attribute->nodeName) || $DOMSource->getAttribute('controller') == 'true'){
				$DOMTarget->setAttribute($attribute->nodeName, $attribute->nodeValue);
			}
		}
	}
}


//*****************************************************************//
//************ UnknownCorelibManagerExtension class ***************//
//*****************************************************************//
/**
 * Corelib manager unknown extension class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @internal
 */
class UnknownCorelibManagerExtension extends CorelibManagerExtension {


	//*****************************************************************//
	//******* UnknownCorelibManagerExtension class properties *********//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var UnknownCorelibManagerExtension
	 * @internal
	 */
	private static $instance = null;


	//*****************************************************************//
	//********* UnknownCorelibManagerExtension class methods **********//
	//*****************************************************************//
	/**
	 * 	Return instance of UnknownCorelibManagerExtension.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses UnknownCorelibManagerExtension::$instance
	 *	@return UnknownCorelibManagerExtension
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new UnknownCorelibManagerExtension();
		}
		return self::$instance;
	}
}


//*****************************************************************//
//************************* Manager class *************************//
//*****************************************************************//
/**
 * Corelib manager class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @internal
 */
class Manager implements Singleton {


	//*****************************************************************//
	//******************* Manager class properties ********************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var Manager
	 * @internal
	 */
	private static $instance = null;

	/**
	 * @var array list of directorys to search
	 * @internal
	 */
	protected $extension_dirs = array(CORELIB,
	                                  'var/',
	                                  'lib/',
	                                  'share/');
	/**
	 * @var DOMDocument extensions.xml DOMDocument
	 * @internal
	 */
	private $extensions = null;

	/**
	 * @var DOMDocument extension settings DOMDocument
	 * @internal
	 */
	private $settings = null;

	/**
	 * @var string manager datadir
	 * @internal
	 */
	private $datadir = MANAGER_DATADIR;

	/**
	 * @var string extension file.
	 * @internal
	 */
	private $extension_file = '';

	/**
	 * @var string extension settings file.
	 * @internal
	 */
	private $extension_setting_file = '';

	/**
	 * @var array extension data
	 * @internal
	 */
	private $extensions_data = array();


	//*****************************************************************//
	//******************** Manager class constants ********************//
	//*****************************************************************//
	/**
	 * @var string extension filename
	 * @internal
	 */
	const EXTENSIONS_FILE = 'extensions.xml';

	/**
	 * @var string extension filename
	 * @internal
	 */
	const EXTENSION_SETTINGS_FILE = 'extension-settings.xml';


	//*****************************************************************//
	//********************* Manager class methods *********************//
	//*****************************************************************//
	/**
	 * Create new instance of Manager.
	 *
	 * @param string $datadir
	 * @return void
	 * @internal
	 */
	protected function __construct($datadir=null){
		if(!is_null($datadir)){
			$this->datadir = $datadir;
		}
		if(!is_dir($this->datadir)){
			mkdir($this->datadir, 0777, true);
			@chmod($this->datadir, 0777);
		}
		if(!is_writeable($this->datadir)){
			trigger_error($this->datadir.' is read-only', E_USER_ERROR);
		}
	}

	/**
	 * 	Return instance of Manager.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses Manager::$instance
	 *	@return Manager
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Manager();
		}
		return self::$instance;
	}

	/**
	 * Initiate manager.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function init(){
		$this->extension_file = $this->datadir.self::EXTENSIONS_FILE;
		$this->extension_settings_file = $this->datadir.self::EXTENSION_SETTINGS_FILE;

		$this->_loadExtensionSettings();

		if(!is_file($this->extension_file) || MANAGER_DEVELOPER_MODE){
			$this->_reloadManagerExtensions(true);
		} else {
			$this->_reloadManagerExtensionsData();
		}

		return true;
	}

	/**
	 * Add extension dir.
	 *
	 * Add a new directory where the {@link Manager} should
	 * search for extensions.
	 *
	 * @param string $dir
	 * @return boolean true on success, else return false
	 */
	public function addExtensionDir($dir){
		$this->extension_dirs[] = $dir;
		return true;
	}

	/**
	 * Setup page registry.
	 *
	 * @param array $pages page registry
	 * @return boolean true on success, else return false
	 * @internal
	 */
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

			$engine = $page->getElementsByTagName('engine');
			if($engine->length > 0){
				$p['engine'] = $engine->item(0)->nodeValue;
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

	/**
	 * Get resource filename.
	 *
	 * @param string $handle handle name
	 * @param string $resource filename
	 * @return string if file exists, else return false
	 * @internal
	 */
	public function getResource($handle, $resource){
		assert('is_string($handle)');
		assert('is_string($resource)');

		$config = ManagerConfig::getInstance();
		if(!$dir = $config->getResourceDir($handle)){
			return false;
		} else if(!is_dir($dir)){
			throw new BaseException('No Such file or directory: '.$dir);
		}

		$filename = $dir.'/'.$resource;
		// $filename = str_replace('../', '/', $filename);
		while(strstr($filename, '//')){
			$filename = str_replace('//', '/', $filename);
		}

		if(!@is_file($filename)){
			return false;
		}
		return $filename;
	}

	/**
	 * Get datadir.
	 *
	 * @return string data directory
	 * @internal
	 */
	public function getDatadir(){
		return $this->datadir;
	}

	/**
	 * Get extensions DOMDocument.
	 *
	 * @return DOMDocument
	 * @internal
	 */
	public function getExtensionsXML(){
		return $this->extensions->documentElement;
	}

	/**
	 * Get extension handler by extension id.
	 *
	 * @return CorelibManagerExtension if extension exists, else return false
	 */
	public function getExtensionHandlerByID($extension){
		if(isset($this->extensions_data[$extension])){
			return $this->extensions_data[$extension]['handler'];
		} else {
			return false;
		}
	}

	/**
	 * Enable extension
	 *
	 * @param CorelibManagerExtension $extension
	 * @return boolean true on success, else return false.
	 */
	public function enableExtension(CorelibManagerExtension $extension){
		$settings = $this->_getExtensionSettings($extension);
		$settings->setAttribute('enabled', 'true');
		$this->_saveExtensionSettings();
		return true;
	}

	/**
	 * Enable extension
	 *
	 * @param CorelibManagerExtension $extension
	 * @return boolean true on success, else return false.
	 */
	public function disableExtension(CorelibManagerExtension $extension){
		$settings = $this->_getExtensionSettings($extension);
		$settings->setAttribute('enabled', 'false');
		$this->_saveExtensionSettings();
		return true;
	}

	/**
	 * Parse constant tags.
	 *
	 * Replace constant tags {CONSTANT} with the actual
	 * constant value.
	 *
	 * @param string $string source
	 * @return string parsed string
	 */
	static public function parseConstantTags($string){
		eval('$string = \''.preg_replace('/\{([A-Za-z_-]+)\}/', '\'.\\1.\'', addcslashes($string, '\'')).'\';');
		return $string;
	}

	/**
	 * Reload manager extension data.
	 *
	 * @param boolean $install treat reload as a new install
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _reloadManagerExtensionsData($install = false){
		$this->extensions_data = array();
		$this->_loadExtensionsXML();
		$event = EventHandler::getInstance();
		$xpath = new DOMXPath($this->extensions);
		$properties = $this->extensions->getElementsByTagName('extension');
		for ($i = 0; $item = $properties->item($i); $i++){
			$id = $properties->item($i)->getAttribute('id');
			if($setup = $item->getElementsByTagName('setup')->item(0)){
				$handler = $setup->getElementsByTagName('handler');
				if($handler->length > 0){
					$handler = call_user_func($handler->item(0)->nodeValue.'::getInstance');
					$handler->setManager($this);
					$handler->setID($id);
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
									$event->trigger(new ManagerUnknownSetupProperty($handler, $prop));
								}
								break;
						}
					}
				} else {
					for ($p = 0; $prop = $setup->childNodes->item($p); $p++){
						if($prop->nodeType != XML_TEXT_NODE){
							$event->trigger(new ManagerUnknownSetupProperty(UnknownCorelibManagerExtension::getInstance(), $prop));
						}
					}
					$handler = null;
				}


				if(is_null($id)){
					throw new BaseException('Extension id not set');
				}
				if(isset($this->extensions_data[$id])){
				 	throw new BaseException('Extension id not unique: '.$id.' with handler '.get_class($handler).' conflicts with handler '.get_class($this->extensions_data[$id]['handler']));
				}
				$this->extensions_data[$id] = array('handler' => $handler, 'node'=>$item);
			}
		}


		foreach ($this->extensions_data as $extension){
			if($extension['handler'] instanceof CorelibManagerExtension){
				$enable = $extension['node']->getAttribute('enabled');
				if(is_null($enable) || $enable == 'true'){
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
		return true;
	}

	/**
	 * Reload manager extensions.
	 *
	 * @return void
	 * @internal
	 */
	protected function _reloadManagerExtensions($install=false){
		$this->extensions = new DOMDocument('1.0', 'UTF-8');
		$this->extensions->appendChild($this->extensions->createElement('extensions'));

		foreach ($this->extension_dirs as $val){
			$this->_searchDir($val);
		}
		reset($this->extension_dirs);
		@chmod($this->extension_file, 0666);



		// XXX
		// XXX Add settings loader here
		// XXX
		$xpath_extensions = new DOMXPath($this->extensions);
		$xpath_settings = new DOMXPath($this->settings);
		$extension_settings = $xpath_settings->query('//settings/extension');

		for ($i = 0; $item = $extension_settings->item($i); $i++){
			$id = $item->getAttribute('id');

			$extension = $xpath_extensions->query('//extensions/extension[@id=\''.$id.'\']');
			if($extension->length > 0){
				$node = $extension->item(0);
				$enable = $item->getAttribute('enabled');
				if(!is_null($enable)){
					$node->setAttribute('enabled', $enable);
				}
			} else {
				$item->parentNode->removeChild($item);
			}
		}
		$this->settings->save($this->extension_settings_file);

		if(!is_file($this->extension_file) || MANAGER_DEVELOPER_MODE){
			$this->extensions->save($this->extension_file);
		}

		$this->_reloadManagerExtensionsData($install);
	}

	/**
	 * Get extension xml element by extension id.
	 *
	 * @return DOMElement if extension exists, else return false
	 */
	private function _getExtensionNodeByID($extension){
		if(isset($this->extensions_data[$extension])){
			return $this->extensions_data[$extension]['node'];
		} else {
			return false;
		}
	}

	/**
	 * Load extension.xml file.
	 *
	 * @return void
	 */
	private function _loadExtensionsXML(){
		if(!$this->extensions instanceof DOMDocument){
			$this->extensions = new DOMDocument('1.0', 'UTF-8');
			$this->extensions->load($this->extension_file);
		}
	}

	/**
	 * Save extension settings xml file.
	 *
	 * @return void
	 */
	private function _saveExtensionSettings(){
		$this->settings->save($this->extension_settings_file);
		if(is_file($this->extension_file)){
			unlink($this->extension_file);
		}
	}

	/**
	 * Load extension settings xml file.
	 *
	 * @return void
	 */
	private function _loadExtensionSettings(){
		$this->settings = new DOMDocument('1.0', 'UTF-8');
		if(!is_file($this->extension_settings_file)){
			$this->settings->appendChild($this->settings->createElement('settings'));
		} else {
			$this->settings->load($this->extension_settings_file);
		}
	}

	/**
	 * Get extension settings.
	 *
	 * @param CorelibManagerExtension $extension
	 * @return DOMElement
	 */
	protected function _getExtensionSettings(CorelibManagerExtension $extension){
		$xpath = new DOMXPath($this->settings);
		$query = $xpath->query('//settings/extension[@id=\''.$extension->getID().'\']');
		if($query->length == 0){
			$settings = $this->settings->documentElement->appendChild($this->settings->createElement('extension'));
			$settings->setAttribute('id', $extension->getID());
			return $settings;
		} else {
			return $query->item(0);
		}
	}

	/**
	 * Search directory for .cxd files.
	 *
	 * @param string $dir directory
	 * @return void
	 * @internal
	 */
	protected function _searchDir($dir){
		if(is_readable($dir)){
			$d = dir($dir);

			while (false !== ($entry = $d->read())) {
				if(preg_match('/\.cxd$/', $entry)){
					$this->_loadExtension($dir.'/'.$entry);
				} else if(is_dir($dir.'/'.$entry) && $entry != '.' && $entry != '..'){
					$this->_searchDir($dir.'/'.$entry);
				} else if($entry != '.' && $entry != '..'){
					EventHandler::getInstance()->trigger(new ManagerFileSearch($dir.'/'.$entry));
				}
			}
		}
	}

	/**
	 * Load extension file.
	 *
	 * @param string $file
	 * @return DOMElement
	 * @internal
	 */
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

	/**
	 * @ignore
	 */
	private function __clone(){ }
}


//*****************************************************************//
//******************* ManagerFileSearch class *********************//
//*****************************************************************//
/**
 * Corelib manager file search event.
 *
 * This event is triggered each time the {@link Manager::_searchDir()}
 * finds a file that is does not recognise.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 */
class ManagerFileSearch implements Event {


	//*****************************************************************//
	//************** ManagerFileSearch class properties ***************//
	//*****************************************************************//
	/**
	 * @var string found file
	 * @internal
	 */
	private $filename;


	//*****************************************************************//
	//*************** ManagerFileSearch class methods *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $filename
	 * @return void
	 * @internal
	 */
	public function __construct($filename){
		assert('is_string($filename)');
		$this->filename = $filename;
	}

	/**
	 * Get found filename.
	 *
	 * @return string filename
	 */
	public function getFilename(){
		return $this->filename;
	}
}


//*****************************************************************//
//************* ManagerUnknownSetupProperty class *****************//
//*****************************************************************//
/**
 * Corelib manager unknown setup node.
 *
 * This event is triggered each time a unknown node name
 * inside the .cxd file's setup element.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 */
class ManagerUnknownSetupProperty implements Event {


	//*****************************************************************//
	//********* ManagerUnknownSetupProperty class properties **********//
	//*****************************************************************//
	/**
	 * @var DOMNode unknown node
	 * @internal
	 */
	private $property;

	/**
	 * @var CorelibManagerExtension
	 * @internal
	 */
	private $handler;


	//*****************************************************************//
	//********** ManagerUnknownSetupProperty class methods ************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param CorelibManagerExtension $handler
	 * @param DOMNode $property
	 * @return void
	 * @internal
	 */
	public function __construct(CorelibManagerExtension $handler, DOMNode $property){
		$this->property = $property;
		$this->handler = $handler;
	}

	/**
	 * Get unknown DOMElement.
	 *
	 * @return DOMElement
	 */
	public function getProperty(){
		return $this->property;
	}

	/**
	 * Get extension handler.
	 *
	 * @return CorelibManagerExtension
	 */
	public function getHandler(){
		return $this->handler;
	}
	/**
	 * Get unknown property name.
	 *
	 * @return string
	 */
	public function getPropertyName(){
		return $this->property->nodeName;
	}
}


//*****************************************************************//
//************* ManagerUnknownSetupProperty class *****************//
//*****************************************************************//
/**
 * Corelib manager page.
 *
 * Extension HTTP controllers should implement this
 * Page class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 */
abstract class ManagerPage extends PageBase {


	//*****************************************************************//
	//********* ManagerUnknownSetupProperty class properties **********//
	//*****************************************************************//
	/**
	 * DOMXSL template.
	 *
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $xsl = null;

	/**
	 * Post template.
	 *
	 * @var PageFactoryPostTemplate
	 */
	protected $post = null;

	/**
	 * Init page.
	 *
	 * @see PageBase::__init()
	 */
	public function __init(){
		if(!defined('CORELIB_MANAGER_USERNAME')){
			define('CORELIB_MANAGER_USERNAME', 'admin');
		}
		if(!defined('CORELIB_MANAGER_PASSWORD')){
			define('CORELIB_MANAGER_PASSWORD', sha1(RFC4122::generate()));
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
		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			$this->xsl = new PageFactoryDOMXSLTemplate('Base/share/xsl/base/core.xsl');
			$this->addTemplateDefinition($this->xsl);
		} else {
			$this->post = new PageFactoryPostTemplate();
			$this->addTemplateDefinition($this->post);
		}
	}

	/**
	 * Get current page from url.
	 *
	 * @param string $inputvar http get variable name
	 * @return integer page
	 */
	public function getPagingPage($inputvar = 'p'){
		$input = InputHandler::getInstance();
		if($input->validateGet($inputvar,new InputValidatorRegex('/^[0-9]+$/'))) {
			return (int) $input->getGet($inputvar);
		} else {
			return 1;
		}
	}
}
?>