<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Base Classes.
 *
 *	<i>No Description</i>
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
 * @copyright Copyright (c) 2005-2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 5.0.0 ($Id$)
 * @filesource
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
if(!defined('PAGE_FACTORY_CLASS_NAME')){
	/**
	 * Define the default page class.
	 *
	 * This class is name of the class which {@link PageFactory}
	 * looks for when loading pages.
	 *
	 * @var boolean string class name
	 */
	define('PAGE_FACTORY_CLASS_NAME', 'WebPage');
}
if(!defined('PAGE_FACTORY_CACHE_ENABLE')){
	/**
	 * Enable or disable page caching.
	 *
	 * Should PageFactory do page caching?
	 *
	 * @var boolean true if enable, else false
	 * @since Version 5.0
	 */
	define('PAGE_FACTORY_CACHE_ENABLE', false);
}

if(!defined('PAGE_FACTORY_CACHE_DEBUG')){
	/**
	 * Enable cache debugging
	 *
	 * If {@link PAGE_FACTORY_CACHE_ENABLE} is enabled and
	 * this contant i set to boolean true it will cause PageFactory
	 * to create a new cache file on each request.
	 *
	 * @see PAGE_FACTORY_CACHE_ENABLE
	 * @var boolean true if enabled else false
	 */
	define('PAGE_FACTORY_CACHE_DEBUG', false);
}
if(!defined('PAGE_FACTORY_CACHE_DIR')){
	/**
	 * Cache dir.
	 *
	 * @var string directory
	 * @since Version 5.0
	 */
	define('PAGE_FACTORY_CACHE_DIR', 'var/cache/pages/');
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
	/**
	 * Template init method
	 *
	 * This method allows for developers to do some initial actions
	 * as soon as a template has been added to into to the template
	 * engine, the template engine then return boolean true or false
	 * based on if the init method returned true.
	 *
	 * @todo add some references and add more documentation
	 * @return boolean true on success, else return false
	 */
	public function init(){
		return true;
	}

	/**
	 * Get supported template engine.
	 *
	 * Each template can support only one type of template engine,
	 * this allows a single page to implement different template engines
	 * for on the fly template interchange ability.
	 *
	 * @return string Supported template engine class name
	 */
	abstract public function getSupportedTemplateEngineName();

	/**
	 * Cleanup template before sending output.
	 *
	 * Do some last minute stuff before Pagefactory returns or
	 * writes template engine output. This could be sending HTTP headers
	 *
	 * @todo this method should be renamed to something more saying
	 * @return void
	 */
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

	/**
	 * @var CacheManager
	 */
	private $cache = null;

	public function build(PageBase $page, $callback=null){

		$this->page = $page;
		$this->page->setCacheManager($this->cache);

		if(!is_null($callback)){
			if(BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL && isset($_GET['CALLBACK'])){
				echo $callback;
			}
			$eval = '$this->page->'.$callback.';';
			eval($eval);
		} else {
			$this->page->build();
		}
	}

	protected function _getCacheType(){
		return $this->cache->getType();
	}
	protected function _isCached(){
		return $this->cache->isCached();
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

	public function setCacheManager(CacheManager $cache){
		$this->cache = $cache;
	}

	abstract public function draw();
	abstract public function getSupportedTemplateDefinition();
	abstract public function addPageContent(Output $content);
	abstract public function addPageSettings(Output $settings);
}


/**
 * @author Steffen Sørensen <ss@corelib.org>
 */
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

	/**
	 * @var string requested page url.
	 */
	private $url = null;

	private $cache = null;


	private $write_to_cache = false;


	/**
	 * @return void
	 */
	private function __construct(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			PageFactoryDeveloperToolbar::getInstance()->addItem(new PageFactoryDeveloperToolbarItemExectutionTimeCalculator());
		}

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
			$this->url = preg_replace('/^'.str_replace('/', '\/', preg_quote($dirname)).'/', '', $this->url);
		}
		$this->cache = new CacheManager(PAGE_FACTORY_CACHE_DIR.str_replace('/', '_', $_SERVER['REQUEST_URI']));
		$this->engine->setCacheManager($this->cache);

		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			PageFactoryDeveloperToolbar::getInstance()->addItem(new PageFactoryDeveloperToolbarItemCacheStatus($this->cache));
		}
	}

	/**
	 * Get PageFactory instance.
	 *
	 * @uses PageFactory::$instance
	 * @return PageFactory
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PageFactory();
		}
		return self::$instance;
	}

	/**
	 * Bootstrap page.
	 *
	 * If param $return is set to false the boostrapping process will
	 * echo the result returned from the page drawing process and
	 * return true. if $return is true the result will be returned instead.
	 * Please note that if a PHP have occured during the draw process the
	 * will return false.
	 *
	 * In the end it will draw the DeveloperToolbar.
	 *
	 * @todo Developer toolbar still neads a lot of work especially when handling different kinds of output.
	 * @param $return boolean if true return PageFactory output else print echo to browser
	 * @since Version 5.0
	 * @uses Eventhandler::triggerEvent()
	 * @uses EventRequestStart
	 * @uses EventRequestEnd
	 * @uses PageFactory::resolvePageObject()
	 * @uses PageFactory::getCacheType()
	 * @uses PageFactory::draw()
	 * @uses PAGE_FACTORY_CACHE_STATIC
	 * @uses PAGE_FACTORY_CLASS_NAME
	 * @uses BaseException::getErrorPage()
	 * @uses BaseException::IsErrorThrown()
	 * @return mixed string or boolean
	 */
	public static function bootstrap($return=false){
		header('Content-Type: text/html');

		$eventHandler = EventHandler::getInstance();
		$eventHandler->triggerEvent(new EventRequestStart());

		$page = PageFactory::getInstance();
 		$page->resolvePageController();

		if($page->getCacheType() != PAGE_FACTORY_CACHE_STATIC){
			try {
				if(class_exists(PAGE_FACTORY_CLASS_NAME, false)){
					eval('$page->build(new '.PAGE_FACTORY_CLASS_NAME.'());');
				} else {
					throw new BaseException('Could not find WebPage Class.');
				}
			} catch (BaseException $e) {
				echo $e;
			}
		}

		if(BaseException::IsErrorThrown()){
			echo BaseException::getErrorPage();
			$data = false;
		} else {
			if($return){
				$data = $page->draw($return);
			} else {
				$page->draw($return);
				$data = true;
			}
		}
		$eventHandler->triggerEvent(new EventRequestEnd());
		echo PageFactoryDeveloperToolbar::getInstance();
		return $data;
	}

	/**
	 * Add page resolver.
	 *
	 * @param string $ident
	 * @param PageFactoryPageResolver $resolver
	 * @return PageFactoryPageResolver on success else return boolean false
	 */
	public function addResolver($ident, PageFactoryPageResolver $resolver){
		assert('is_string($ident)');
		$this->resolvers[$ident] = $resolver;
		return $resolver;
	}

	/**
	 * Resolve page object.
	 *
	 * Deprecated, use {@link PageFactory::resolvePageController()} instead
	 *
	 * @see PageFactory::resolvePageController();
	 * @deprecated Since version 5.0
	 * @return true if page object could be found
	 */
	public function resolvePageObject(){
		return $this->resolvePageController();
	}

	/**
	 * Resolve page controller.
	 *
	 * Use configuration page lookup tables to find the file containing
	 * the page controller which controls the current url.
	 * the default files for this is either {@link get.php} for get requests
	 * and {@link post.php} for post requests.
	 *
	 * This function is used by older versions of the corelib Dummy, however
	 * the corelib Dummy now uses {@link PageFactory::bootstrap()} instead
	 *
	 * @throws BaseException if no page object could be found.
	 * @return true if page controller could be found
	 * @since Version 5.0
	 * @see PageFactory::bootstrap()
	 */
	public function resolvePageController(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			include_once(PAGE_FACTORY_POST_FILE);
		} else {
			if(PAGE_FACTORY_CACHE_ENABLE && $this->cache->isCached() && !PAGE_FACTORY_CACHE_DEBUG){
				if(!is_executable($this->cache->getFilename())){
					$this->cache->setType(PAGE_FACTORY_CACHE_STATIC);
					return true;
				} else {
					$this->cache->setType(PAGE_FACTORY_CACHE_DYNAMIC);
					include_once(PAGE_FACTORY_GET_FILE);
				}
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
							$this->_setCacheSettings($val);
							$this->callback = preg_replace($val['expr'], $val['exec'], addcslashes($this->url, '\''));
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
			$this->url = '/404/';
		}
		if(is_array($pages[$this->url])){
			if(!isset($pages[$this->url]['page'])){
				throw new BaseException('file not set.', E_USER_ERROR);
			}
			if(!isset($pages[$this->url]['exec'])){
				$pages[$this->url]['exec'] = 'build';
			}
			$page = $pages[$this->url]['page'];
			$this->callback = $pages[$this->url]['exec'].'()';

			$this->_setCacheSettings($pages[$this->url]);
		} else {
			$page = $pages[$this->url];
		}

		if(!is_file($page)){
			throw new BaseException('Unable to open: '.$page.'. File not found.', E_USER_ERROR);
		}

		require_once($page);
		return true;
	}

	/**
	 * Get current caching type.
	 *
	 * @uses PAGE_FACTORY_CACHE_DEBUG
	 * @uses PAGE_FACTORY_CACHE_ENABLE
	 * @uses PAGE_FACTORY_CACHE_DYNAMIC
	 * @uses CacheManager::isCached()
	 * @uses CacheManager::getType()
	 * @uses PageFactory::$cache
	 * @return integer Cache type
	 */
	public function getCacheType(){
		if(PAGE_FACTORY_CACHE_ENABLE && !PAGE_FACTORY_CACHE_DEBUG && $this->cache->isCached()){
			return $this->cache->getType();
		} else {
			return PAGE_FACTORY_CACHE_DYNAMIC;
		}
	}


	/**
	 * @param array $page
	 * @return void
	 */
	private function _setCacheSettings(array $page){
		if(isset($page['cache'])){
			$this->cache->setType($page['cache']);
			if(isset($page['ttl'])){
				$this->cache->setTTL($page['ttl']);
			}
			$this->write_to_cache = true;
		} else {
			$this->cache->setType(PAGE_FACTORY_CACHE_DISABLED);
		}
	}

	/**
	 * @param PageBase $page
	 * @return void
	 */
	public function build(PageBase $page){
		$page->setCacheManager($this->cache);
		$page->__init();
		$this->engine->build($page, $this->callback);
	}

	/**
	 * @param boolean $return
	 * @return mixed
	 */
	public function draw($return=false){
		if($this->getCacheType() == PAGE_FACTORY_CACHE_STATIC && $this->cache->isCached()){
			if($return){
				return $this->cache->read();
			} else {
				echo $this->cache->read();
			}
		} else {
			if(!($this->getCacheType() == PAGE_FACTORY_CACHE_DYNAMIC && $this->cache->isCached()) || !PAGE_FACTORY_CACHE_ENABLE){
				$content = $this->engine->draw();
				if($template = $this->engine->getTemplate()){
					$template->cleanup();
				}
				if($this->write_to_cache && PAGE_FACTORY_CACHE_ENABLE){
					$this->cache->setData($content);
				}
			}

			if($this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
				echo $this->cache->read();
				return true;
			}

			if($return){
				return $content;
			} else {
				echo $content;
				return true;
			}
		}

	}
}

/**
 * Parsetime calculator toolbox item.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package corelib
 * @subpackage Base
 */
class PageFactoryDeveloperToolbarItemExectutionTimeCalculator extends PageFactoryDeveloperToolbarItem {
	private $start = null;

	public function __construct(){
		$this->start = microtime(true);
	}

	public function getToolbarItem(){
		return '<img src="corelib/resource/manager/images/page/icons/toolbar/parsetime.png" alt="parsetime" title="Page execution time"/> '.(round((microtime(true) - $this->start) , 4) * 1000).' ms.';
	}

	public function getContent(){
		return false;
	}
}
?>