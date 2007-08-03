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
 *	@author Steffen Sørensen <steffen@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 *	@version 1.0.0 ($Id$)
 */

if(!defined('PAGE_FACTORY_ENGINE')){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		define('PAGE_FACTORY_ENGINE', 'PageFactoryPost');
	} else {
		define('PAGE_FACTORY_ENGINE', 'PageFactoryDOMXSL');
	}
}
if(!defined('PAGE_FACTORY_CACHE_ENABLE')){
	define('PAGE_FACTORY_CACHE_ENABLE', false);
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

	public function build(Page $page, $callback=null){
		$this->page = $page;
		if(!is_null($callback)){
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
		if(!isset($_GET['page'])){
			$_GET['page'] = '/';
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			include_once('etc/post.php');
		} else {
			if(PAGE_FACTORY_CACHE_ENABLE){
				include_once(CORELIB.'/Base/Lib/PageFactory/CacheManager.php');
				return true;
			} else {
				include_once('etc/get.php');
			}
		}

		if(substr($_GET['page'], -1) != '/'){
			$_GET['page'] .= '/';
		}
		if(preg_match('/^\/corelib/', $_GET['page'])){
			$manager = Manager::getInstance();
			$manager->setupPageRegistry($pages, $rpages);
		}
		if(!isset($pages[$_GET['page']])){
			if(isset($rpages)){
				while(list(,$val) = each($rpages)){
					if($val['type'] != 'regex'){
						$this->resolvers[$val['type']]->resolve($val['expr'], $val['exec']);
						$val['expr'] = $this->resolvers[$val['type']]->getExpression();
						$val['exec'] = $this->resolvers[$val['type']]->getExecute();
					}
					if(preg_match($val['expr'], $_GET['page'])){
						try {
							if(!is_file($val['page'])){
								throw new BaseException('Unable to open: '.$val['page'].'. File not found.', E_USER_ERROR);
							}
						} catch (BaseException $e){
							echo $e;
							exit;
						}
						$this->callback = preg_replace($val['expr'], $val['exec'], $_GET['page']);
						require_once($val['page']);
						return true;
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
			require_once($pages['/404/']);
			return true;
		} else {
			if(is_array($pages[$_GET['page']])){
				try {
					if(!isset($pages[$_GET['page']]['page'])){
						throw new BaseException('file not set.', E_USER_ERROR);
					}
					if(!isset($pages[$_GET['page']]['exec'])){
						throw new BaseException('exec not set.', E_USER_ERROR);
					}
				} catch (BaseException $e){
					echo $e;
					exit;
				}
				$page = $pages[$_GET['page']]['page'];
				$this->callback = $pages[$_GET['page']]['exec'].'()';
			} else {
				$page = $pages[$_GET['page']];
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

	public function build(Page $page){
		$this->engine->build($page, $this->callback);
	}

	public function draw(){
		$this->engine->draw();
		if($template = $this->engine->getTemplate()){
			$template->cleanup();
		}
	}
}
?>