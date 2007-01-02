<?php
if(!defined('MANAGER_DATATIR')){
	define('MANAGER_DATADIR', 'var/db/manager/');
}
if(!defined('MANAGER_DEVELOPER_MODE')){
	define('MANAGER_DEVELOPER_MODE', false);
}

class Manager implements Singleton,Output {
	private static $instance = null;

	private $extension_dirs = array(CORELIB);
	private $extensions = array();
	private $pages = array();
	private $rpages = array();
	private $menu = array();
	private $resources = array();
	
	const PAGE_REGISTRY_FILE = 'pages.db';
	const MENU_REGISTRY_FILE = 'menu.db';
	const RESOURCE_REGISTRY_FILE = 'resources.db';
	const CONSTANT_REGISTRY_FILE = 'constants.db';
	const REGISTRY_FILE = 'registry.db';

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
		$this->_loadManagerExtensions();
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

	public function setupPageRegistry(&$pages, &$rpages){
		$pages = $this->pages;
		$rpages = $this->rpages;
	}
	public function addExtensionDir($dir){
		$this->extension_dirs[] = $dir;
	}
	public function reloadManagerExtensions(){
		$this->pages = array();
		$this->extensions = array();
		$this->pages = array();
		$this->rpages = array();
		$this->menu = array();
	
		while (list(,$val) = each($this->extension_dirs)) {
			$this->_searchDir($val);
		}
		reset($this->extension_dirs);
		
		while (list(,$extension) = each($this->extensions)) {
			if($extension['enabled']){
				while (list($url, $page) = each($extension['pages'])) {
					$this->pages[$url] = $page;
				}
				while (list(, $rpage) = each($extension['rpages'])) {
					$this->rpages[] = $rpage;
				}
				while (list($handle, $resource) = each($extension['resources'])) {
					$this->resources[$handle] = $resource;
				}
				while (list($title, $group) = each($extension['menu'])){
					if(!isset($this->menu[$title])){
						$this->menu[$title] = array();
					}
					while (list(, $item) = each($group)){
						$this->menu[$title][] = $item;
					}
				}
			}
		}
		reset($this->extensions);
		
		file_put_contents(MANAGER_DATADIR.self::REGISTRY_FILE, serialize($this->extensions));
		file_put_contents(MANAGER_DATADIR.self::MENU_REGISTRY_FILE, serialize($this->menu));
		file_put_contents(MANAGER_DATADIR.self::RESOURCE_REGISTRY_FILE, serialize($this->resources));
		file_put_contents(MANAGER_DATADIR.self::PAGE_REGISTRY_FILE, serialize($this->pages)."\n".serialize($this->rpages));
	}
	public function getResource($handle, $resource){
		try {
			StrictTypes::isString($handle);
			StrictTypes::isString($resource);
			if(!isset($this->resources[$handle])){
				throw new BaseException('Unknown Resource Handle: '.$handle);
			} else if(!is_dir($this->resources[$handle])){
				throw new BaseException('No Such file or directory: '.$this->resources[$handle]);
			}
			$filename = $this->resources[$handle].'/'.$resource;
			$filename = str_replace('../', '/', $filename);
			while(strstr($filename, '//')){
				$filename = str_replace('//', '/', $filename);
			}
			if(!is_file($filename)){
				throw new BaseException('No Such file or directory: '.$filename);
			}
		} catch (BaseException $e){
			echo $e;
			exit;
		}
		return $filename;
	}

	private function _loadManagerExtensions(){
		if(!is_file(MANAGER_DATADIR.self::REGISTRY_FILE) || !is_file(MANAGER_DATADIR.self::MENU_REGISTRY_FILE) || !is_file(MANAGER_DATADIR.self::PAGE_REGISTRY_FILE) || MANAGER_DEVELOPER_MODE){
			$this->reloadManagerExtensions();
		}
		$this->extensions = unserialize(file_get_contents(MANAGER_DATADIR.self::REGISTRY_FILE));
		$this->menu = unserialize(file_get_contents(MANAGER_DATADIR.self::MENU_REGISTRY_FILE));
		$this->resources = unserialize(file_get_contents(MANAGER_DATADIR.self::RESOURCE_REGISTRY_FILE));
		$pages = file_get_contents(MANAGER_DATADIR.self::PAGE_REGISTRY_FILE);
		list($pages, $rpages) = explode("\n", $pages);
		$this->pages = unserialize($pages);
		$this->rpages = unserialize($rpages);
	}
	private function _searchDir($dir){
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if(preg_match('/\.mext$/', $entry)){
				$this->_loadExtension($dir.'/'.$entry);
			} else if($entry == 'constants.xml'){
				$this->_loadConstants($dir.'/'.$entry);
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
		$enabled = $dom->documentElement->getAttribute('enabled');
		if($id = $dom->documentElement->getAttribute('id')){
			$name = $dom->documentElement->getElementsByTagName('name');
			if($name->length > 0){
				if($enabled){
					$extension['enabled'] = true;
				} else {
					$extension['enabled'] = false;
				}
				$name = $name->item(0);
				$extension['name'] = trim($name->nodeValue);
				$extension['description'] = '';
				$desc = $dom->documentElement->getElementsByTagName('description');
				if($desc->length > 0){
					$desc = $desc->item(0);
					$extension['description'] = trim($desc->nodeValue);
				}
				
				$extension['menu'] = array();
				$menu = $dom->documentElement->getElementsByTagName('menu');
				if($menu->length > 0){
					$menu = $menu->item(0);
					$i = 0;
					while($group = $menu->childNodes->item($i++)){
						if($group->nodeName == 'group'){
							$groupname = $group->getAttribute('title');
							$extension['menu'][$groupname] = array();
							$mi = 0;
							while($item = $group->childNodes->item($mi++)){
								if($item->nodeName == 'item'){
									$extension['menu'][$groupname][] = array('url'=>$item->getAttribute('url'),
									                                         'title'=>trim($item->nodeValue));
								}
							}
						}
					}
				}
				$extension['resources'] = array();
				$resources = $dom->documentElement->getElementsByTagName('resources');
				if($resources->length > 0){
					$resources = $resources->item(0);
					$i = 0;
					while($resource = $resources->childNodes->item($i++)){
						if(is_callable(array($resource, 'getAttribute')) && $handle = $resource->getAttribute('handle')){
							if($resource->nodeName == 'resourcedir'){
								$dir = $resource->childNodes->item(0);
								$dir = trim($resource->nodeValue);
								$extension['resources'][$handle] = str_replace('CORELIB', CORELIB, $dir);
							}
						}
					}
				}
				
				$extension['pages'] = array();
				$extension['rpages'] = array();
				$pages = $dom->documentElement->getElementsByTagName('pages');
				if($pages->length > 0){
					$pages = $pages->item(0);
					$i = 0;
					while($page = $pages->childNodes->item($i++)){
						if($page->nodeName == 'page'){
							$exec = $page->getElementsByTagName('exec');
							if($exec->length > 0){
								$exec = $exec->item(0);
								$exec = $exec->nodeValue;
							} else {
								$exec = false;
							}
							$url = $page->getElementsByTagName('url');
							if($url->length > 0){
								$url = $url->item(0);
								$url = $url->nodeValue;
							} else {
								$url = false;
							}
							$file = $page->getElementsByTagName('file');
							if($file->length > 0){
								$file = $file->item(0);
								$file = $file->nodeValue;
							} else {
								$file = false;
							}
							if(!empty($file) && !empty($url)){
								if(empty($exec)){
									$extension['pages'][$url] = str_replace('CORELIB', CORELIB, $file);
								} else {
									$extension['pages'][$url] = array('page'=>str_replace('CORELIB', CORELIB, $file),
									                                  'exec'=>$exec);
								}
							}
						} else if($page->nodeName == 'rpage'){
							$exec = $page->getElementsByTagName('exec');
							if($exec->length > 0){
								$exec = $exec->item(0);
								$exec = $exec->nodeValue;
							} else {
								$exec = false;
							}
							$expr = $page->getElementsByTagName('expr');
							if($expr->length > 0){
								$expr = $expr->item(0);
								$expr = $expr->nodeValue;
							} else {
								$expr = false;
							}
							$file = $page->getElementsByTagName('file');
							if($file->length > 0){
								$file = $file->item(0);
								$file = $file->nodeValue;
							} else {
								$file = false;
							}
							if(!$type = $page->getAttribute('type')){
								$type = false;
							}
							if(!empty($file) && !empty($exec) && !empty($expr) && !empty($type)){
								if(empty($exec)){
									$extension['rpages'][] = $file;
								} else {
									$extension['rpages'][] = array('type'=>$type,
									                               'expr'=>$expr,
									                               'exec'=>$exec,
									                               'page'=>str_replace('CORELIB', CORELIB, $file));
								}
							}	
						}
					}
				}
				$this->extensions[] = $extension;
			}
		}
	}
	
	private function _loadConstants($file){
/*		echo '<pre>';
		echo htmlentities(file_get_contents($file));
		
		$constants = new DOMDocument('1.0', 'UTF-8');
		$constants->load($file);
		
		
		exit;*/
	}
	
	public function getXML(DOMDocument $xml){
		$menu = $xml->createElement('managermenu');
		while (list($name, $group) = each($this->menu)) {
			$DOMgroup = $menu->appendChild($xml->createElement('group'));
			$DOMgroup->setAttribute('title', $name);
			while (list(,$item) = each($group)) {
				$DOMitem = $DOMgroup->appendChild($xml->createElement('item', $item['title']));
				$DOMitem->setAttribute('url', $item['url']);
			}
		}
		reset($this->menu);
		return $menu;
	}
	public function &getArray(){
		
	}
}

class ManagerFileSearch implements Event {
		
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