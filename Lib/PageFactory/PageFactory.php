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
if(!defined('PAGE_FACTORY_GET_TOKEN')){
	define('PAGE_FACTORY_GET_TOKEN', 'REQUESTPAGE');
}

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

	private function __construct(){
		$engine = '$this->engine = new '.PAGE_FACTORY_ENGINE.'();';
		eval($engine);
		$this->addResolver('meta', new MetaResolver());
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
		if(!isset($_GET[PAGE_FACTORY_GET_TOKEN])){
			$_GET[PAGE_FACTORY_GET_TOKEN] = '/';
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			include_once(PAGE_FACTORY_POST_FILE);
		} else {
			// TODO finish cache implementation
/*			if(PAGE_FACTORY_CACHE_ENABLE){
				include_once(CORELIB.'/Base/Lib/PageFactory/CacheManager.php');
				return true;
			} else { */
				include_once(PAGE_FACTORY_GET_FILE);
			// }
		}

		if(substr($_GET[PAGE_FACTORY_GET_TOKEN], -1) != '/'){
			$_GET[PAGE_FACTORY_GET_TOKEN] .= '/';
		}
		if(preg_match('/^\/corelib/', $_GET[PAGE_FACTORY_GET_TOKEN])){
			$manager = Manager::getInstance();
			$manager->setupPageRegistry($pages);
		}
		if(!isset($pages[$_GET[PAGE_FACTORY_GET_TOKEN]])){
			if(isset($pages)){
				foreach($pages as $val){
					if(is_array($val)){
						if( isset($val['type']) && $val['type'] != 'regex' ){
							var_dump($val);
							$this->resolvers[$val['type']]->resolve($val['expr'], $val['exec']); 
							$val['expr'] = $this->resolvers[$val['type']]->getExpression();
							$val['exec'] = $this->resolvers[$val['type']]->getExecute();
						}
						if( isset($val['expr']) && preg_match($val['expr'], $_GET[PAGE_FACTORY_GET_TOKEN]) ){
							try {
								if(!is_file($val['page'])){
									throw new BaseException('Unable to open: '.$val['page'].'. File not found.', E_USER_ERROR);
								}
							} catch (BaseException $e){
								echo $e;
								exit;
							}
							$this->callback = preg_replace($val['expr'], $val['exec'], $_GET[PAGE_FACTORY_GET_TOKEN]);
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
			if(is_array($pages[$_GET[PAGE_FACTORY_GET_TOKEN]])){
				try {
					if(!isset($pages[$_GET[PAGE_FACTORY_GET_TOKEN]]['page'])){
						throw new BaseException('file not set.', E_USER_ERROR);
					}
					if(!isset($pages[$_GET[PAGE_FACTORY_GET_TOKEN]]['exec'])){
						throw new BaseException('exec not set.', E_USER_ERROR);
					}
				} catch (BaseException $e){
					echo $e;
					exit;
				}
				$page = $pages[$_GET[PAGE_FACTORY_GET_TOKEN]]['page'];
				$this->callback = $pages[$_GET[PAGE_FACTORY_GET_TOKEN]]['exec'].'()';
			} else {
				$page = $pages[$_GET[PAGE_FACTORY_GET_TOKEN]];
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

	public function build(PageBase $page){
		$this->engine->build($page, $this->callback);
	}

	public function draw($return=false){
		if($return){
			$content = $this->engine->draw();
		} else {
			echo $this->engine->draw();
		}
		if($template = $this->engine->getTemplate()){
			$template->cleanup();
		}
		if($return){
			return $content;
		}
	}
}
?>