<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Base Classes
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the
 *	Bravura Distribution license that is available through the
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura License and are
 *	unable to obtain it through the web, please send a note to
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 *
 *	@author Steffen Sørensen <ss@corelib.org>
 *	@copyright Copyright (c) 2006 Back in five minutes
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.corelib.org/
 *	@version 1.0.0 ($Id$)
 */

if(!defined('PAGE_FACTORY_ENGINE')){
	if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
		/**
		 * @ignore
		 */
		define('PAGE_FACTORY_ENGINE', 'PageFactoryPost');
	} else {
		define('PAGE_FACTORY_ENGINE', 'PageFactoryDOMXSL');
	}
}
if(!defined('PAGE_FACTORY_CACHE_ENABLE')){
	define('PAGE_FACTORY_CACHE_ENABLE', false);
}
if(!defined('PAGE_FACTORY_CLASS_NAME')){
	define('PAGE_FACTORY_CLASS_NAME', 'WebPage');
}
if(!defined('PAGE_FACTORY_CACHE_DEBUG')){
	define('PAGE_FACTORY_CACHE_DEBUG', false);
}
if(!defined('PAGE_FACTORY_CACHE_DIR')){
	define('PAGE_FACTORY_CACHE_DIR', 'var/db/cache/');
}
if(!defined('PAGE_FACTORY_GET_FILE')){
	define('PAGE_FACTORY_GET_FILE', 'etc/get.php');
}
if(!defined('PAGE_FACTORY_POST_FILE')){
	define('PAGE_FACTORY_POST_FILE', 'etc/post.php');
}
if(!defined('PAGE_FACTORY_SERVER_TOKEN')){
	define('PAGE_FACTORY_SERVER_TOKEN', 'SCRIPT_URL');
}
if(!defined('PAGE_FACTORY_GET_TOKEN')){
	define('PAGE_FACTORY_GET_TOKEN', 'REQUESTPAGE');
}

define('PAGE_FACTORY_CACHE_STATIC', 1);
define('PAGE_FACTORY_CACHE_DYNAMIC', 2);
define('PAGE_FACTORY_CACHE_DISABLED', 3);

/**
 * @todo Implement a redirect resolver based on regular expressions
 */
interface PageFactoryPageResolver {
	public function resolve($expr, $exec);
	public function getExpression();
	public function getExecute();
}

abstract class PageFactoryTemplate {
	public function init(){
		return true;
	}

	abstract public function getSupportedTemplateEngineName();
	abstract public function cleanup();
}

abstract class PageFactoryTemplateEngine {
	/**
	 * @var Page
	 */
	protected $page = null;
	/**
	 * @var PageFactoryTemplate
	 */
	protected $template = null;

	public function build(PageBase $page, $callback=null){
		$this->page = $page;
		if(!is_null($callback)){
			if(BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL && isset($_GET['CALLBACK'])){
				echo $callback;
			}
			eval('$this->page->'.$callback.';');
		} else {
			$this->page->build();
		}
	}

	public function setTemplate(PageFactoryTemplate $template){
		$this->template = $template;
		return $this->template->init();
	}
	/**
	 * @return PageFactoryTemplate
	 */
	public function getTemplate(){
		return $this->template;
	}

	abstract public function draw();
	abstract public function getSupportedTemplateDefinition();
	abstract public function addPageContent(Output $content);
	abstract public function addPageSettings(Output $settings);
}


class PageFactory implements Singleton {
	/**
	 *	@var PageFactory
	 */
	private static $instance = null;
	/**
	 * @var PageFactoryTemplateEngine
	 */
	private $engine = null;

	private $callback = null;

	private $resolvers = array();

	private $url = null;
	
	private $cache = array('type' => PAGE_FACTORY_CACHE_DISABLED);
	
	private $cache_file = null;
	
	private $write_to_cache = false;
	
	private function __construct(){
		$engine = '$this->engine = new '.PAGE_FACTORY_ENGINE.'();';
		eval($engine);
		$this->addResolver('meta', new MetaResolver());
		
		if(!isset($_SERVER[PAGE_FACTORY_SERVER_TOKEN])){
			if(isset($_GET[PAGE_FACTORY_GET_TOKEN])){
				$this->url = $_GET[PAGE_FACTORY_GET_TOKEN];
				$_SERVER[PAGE_FACTORY_SERVER_TOKEN] = $_GET[PAGE_FACTORY_GET_TOKEN];
			} else {
				$this->url = '/';
			}
		} else {
			$this->url = $_SERVER[PAGE_FACTORY_SERVER_TOKEN];
		}
		if(substr($this->url, -1) != '/'){
			$this->url .= '/';
		}
		$dirname = dirname($_SERVER['SCRIPT_NAME']);
		if($dirname != '/'){
			$this->url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $this->url);
		}
		$this->cache_file = 'var/cache/pages/'.str_replace('/', '_', $_SERVER['REQUEST_URI']);
	}

	/**
	 *	@return PageFactory
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PageFactory();
		}
		return self::$instance;
	}

	public function addResolver($ident, PageFactoryPageResolver $resolver){
		try {
			StrictTypes::isString($ident);
		} catch (BaseException $e){
			echo $e;
		}
		$this->resolvers[$ident] = $resolver;
	}

	public function resolvePageObject(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			include_once(PAGE_FACTORY_POST_FILE);
		} else {
			if(PAGE_FACTORY_CACHE_ENABLE && is_file($this->cache_file)){
				if(!is_executable($this->cache_file)){
					$this->cache = array('type' => PAGE_FACTORY_CACHE_STATIC,
					                     'file' => $this->cache_file);
				} else {
					$this->cache = array('type' => PAGE_FACTORY_CACHE_DYNAMIC,
					                     'file' => $this->cache_file);
				}
				return true;
			} else {
				include_once(PAGE_FACTORY_GET_FILE);
			}
		}

		if(preg_match('/^\/corelib/', $this->url)){
			$manager = Manager::getInstance();
			$manager->init();
			$manager->setupPageRegistry($pages);
		}
		
		if(!isset($pages[$this->url])){
			if(isset($pages)){
				foreach($pages as $val){
					if(is_array($val)){
						if(isset($val['type']) && $val['type'] != 'regex' ){
							$this->resolvers[$val['type']]->resolve($val['expr'], $val['exec']); 
							$val['expr'] = $this->resolvers[$val['type']]->getExpression();
							$val['exec'] = $this->resolvers[$val['type']]->getExecute();
						}
						if( isset($val['expr']) && preg_match($val['expr'], $this->url) ){
							try {
								if(!is_file($val['page'])){
									throw new BaseException('Unable to open: '.$val['page'].'. File not found.', E_USER_ERROR);
								}
							} catch (BaseException $e){
								echo $e;
								exit;
							}
							if(isset($val['cache']) && $val['cache'] == PAGE_FACTORY_CACHE_STATIC){
								$this->write_to_cache = true;
							}
							
							$this->callback = preg_replace($val['expr'], $val['exec'], $this->url);
							require_once($val['page']);
							return true;
						}
					}
				}
			}
			
			
			try {
				if(!isset($pages['/404/'])){
					throw new BaseException('404 Error unspecified!', E_USER_ERROR);;
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
			if(is_array($pages['/404/'])){
				require_once($pages['/404/']['page']);
			} else {
				require_once($pages['/404/']);	
			}
			return true;
		} else {
			if(is_array($pages[$this->url])){
				try {
					if(!isset($pages[$this->url]['page'])){
						throw new BaseException('file not set.', E_USER_ERROR);
					}
					if(!isset($pages[$this->url]['exec'])){
						throw new BaseException('exec not set.', E_USER_ERROR);
					}
				} catch (BaseException $e){
					echo $e;
					exit;
				}
				$page = $pages[$this->url]['page'];
				$this->callback = $pages[$this->url]['exec'].'()';
				
				if(isset($pages[$this->url]['cache']) && $pages[$this->url]['cache'] == PAGE_FACTORY_CACHE_STATIC){
					$this->write_to_cache = true;
				}
				
				
			} else {
				$page = $pages[$this->url];
			}
			try {
				if(!is_file($page)){
					throw new BaseException('Unable to open: '.$page.'. File not found.', E_USER_ERROR);
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
			require_once($page);
			return true;
		}
	}

	public function getCacheType(){
		return $this->cache['type'];
	}
	
	public function build(PageBase $page){
		$this->engine->build($page, $this->callback);
	}

	public function draw($return=false){
		if($this->cache['type'] == PAGE_FACTORY_CACHE_STATIC){
			if($return){				
				return file_get_contents($this->cache['file']);
			} else {
				echo file_get_contents($this->cache['file']);
			}
		} else {
			$content = $this->engine->draw();
			if($template = $this->engine->getTemplate()){
				$template->cleanup();
			}
			if(PAGE_FACTORY_CACHE_ENABLE && $this->write_to_cache){
				if(!is_dir(dirname($this->cache_file))){
					mkdir(dirname($this->cache_file), 0777, true);
				}
				file_put_contents($this->cache_file, $content);
			}
			if($return){				
				return $content;
			} else {
				echo $content;
			}
		}
	}
}
?>