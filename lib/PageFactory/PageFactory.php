<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory Base Classes.
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
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//*********************** Define Contants *************************//
//*****************************************************************//
/**
 * Page factory page static caching mode.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_FACTORY_CACHE_STATIC', 1);

/**
 * Page factory page dynamic caching mode.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_FACTORY_CACHE_DYNAMIC', 2);

/**
 * Page factory page caching disabled.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_FACTORY_CACHE_DISABLED', 3);


//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('PAGE_FACTORY_ENGINE')){
	if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST'){
		/**
		 * @ignore
		 */
		define('PAGE_FACTORY_ENGINE', 'PageFactoryPost');
	} else {
		/**
		 * Define the default page factory engine.
		 *
		 * This class is name of the class which {@link PageFactory}
		 * will use to render output
		 *
		 * @var string Page factory engine class name
		 */
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
	 * @since Version 5.0
	 * @var boolean true if enabled else false
	 */
	define('PAGE_FACTORY_CACHE_DEBUG', false);
}
if(!defined('PAGE_FACTORY_CACHE_DIR')){
	/**
	 * Cache dir.
	 *
	 * Define the location of where the cached files should be stored
	 *
	 * @var string directory
	 * @since Version 5.0
	 */
	define('PAGE_FACTORY_CACHE_DIR', BASE_CACHE_DIRECTORY.'pages/');
}
if(!defined('PAGE_FACTORY_GET_FILE')){
	/**
	 * Get request file reference file.
	 *
	 * Define the locations for the GET lookup table
	 *
	 * @var string filename
	 */
	define('PAGE_FACTORY_GET_FILE', 'etc/get.php');
}
if(!defined('PAGE_FACTORY_POST_FILE')){
	/**
	 * Post request file reference file.
	 *
	 * Define the locations for the POST lookup table
	 *
	 * @var string filename
	 */
	define('PAGE_FACTORY_POST_FILE', 'etc/post.php');
}
if(!defined('PAGE_FACTORY_SERVER_TOKEN')){
	/**
	 * Server array entry to use when resolving pages.
	 *
	 * Define which array entry inside $_SERVER to use when
	 * looking for the request url.
	 *
	 * @var string uri relative url
	 */
	define('PAGE_FACTORY_SERVER_TOKEN', 'SCRIPT_URL');
}
if(!defined('PAGE_FACTORY_GET_TOKEN')){
	/**
	 * Get array entry to use when resolving pages.
	 *
	 * In some cases when {@link PAGE_FACTORY_SERVER_TOKEN}
	 * is unavailable a get variable can be used to find the
	 * request url.
	 *
	 * @var string uri relative url
	 */
	define('PAGE_FACTORY_GET_TOKEN', 'REQUESTPAGE');
}


//*****************************************************************//
//************** PageFactoryPageResolver interface ****************//
//*****************************************************************//
/**
 * Page factory resolver interface
 *
 * If a new page factory page lookup table is to be implemented,
 * the resolving class must implement this interface. The exension
 * of the current lookup methods could come in handy if a nother syntax
 * is desired to ease the day to day usage.
 *
 * The way this works is by adding in a expression and a execution statement
 * the resolver then will convert to a regular expression which {@link PageFactory}
 * first will match against the page lookup table and then apply a replace on the
 * same regular expression using the execute statement as replace parameter.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @todo Implement a redirect resolver based on regular expressions
 * @see PageFactory::addResolver()
 */
interface PageFactoryPageResolver {
	/**
	 * Resolve page expression.
	 *
	 * When {@link PageFactory} tries to lookup a page the first
	 * thing it will do is to ask the resolver to parse the the
	 * expression ($expr) and execution statement ($exec) for later use.
	 * The url is also passed along for optimal extendability.
	 *
	 * @param string $expr expression read from page lookup table
	 * @param string $exec execution statement read from page lookup table
	 * @param string $url request url
	 * @return boolean true on success, else return false
	 */
	public function resolve($expr, $exec, $url);

	/**
	 * Get expression.
	 *
	 * Return a regular expression which {@link PageFactory} can use
	 * to match and convert parts the requested url into a valid
	 * {@link http://www.php.net/pcre perl regular expression}.
	 *
	 * @return string {@link http://www.php.net/pcre perl regular expression}
	 */

	public function getExpression();
	/**
	 * Get execution statement.
	 *
	 * Return a valid string which can be used with
	 * {@link http://www.php.net/manual/en/function.preg-replace.php preg_replace}
	 * as the replace paramenter
	 *
	 * @return string
	 */
	public function getExecute();
}


//*****************************************************************//
//************ PageFactoryTemplate abstract classes ***************//
//*****************************************************************//
/**
 * Page factory template class
 *
 * If a new page factory template engine is to be  implemented,
 * a template working with the template engine must be implemented as well.
 * Such template must be extended from this class or a onther class
 * suc as {@link PageFactoryWebAbstractTemplate}, which extends this class.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
abstract class PageFactoryTemplate {
	/**
	 * Template init method
	 *
	 * This method allows for developers to do some initial actions
	 * as soon as a template has been added to into to the template
	 * engine, the template engine then return boolean true or false
	 * based on if the init method returned true. if the init method
	 * returns false, no output will be send.
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


//*****************************************************************//
//********** PageFactoryTemplateEngine abstract class *************//
//*****************************************************************//
/**
 * Page factory template engine
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
abstract class PageFactoryTemplateEngine {


	//*****************************************************************//
	//********** PageFactoryTemplateEngine class properties ***********//
	//*****************************************************************//
	/**
	 * Reference to current PageBase object.
	 *
	 * @var PageBase
	 */
	protected $page = null;

	/**
	 * Page factory template.
	 *
	 * @var PageFactoryTemplate
	 */
	protected $template = null;

	/**
	 * Cache manager reference.
	 *
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;


	//*****************************************************************//
	//************ PageFactoryTemplateEngine class methods ************//
	//*****************************************************************//
	/**
	 * Build page based on PageBase object and callback method.
	 *
	 * @uses PageFactoryTemplateEngine::$page
	 * @uses PageBase::setCacheManager()
	 * @uses PageBase::build()
	 * @param PageBase $page
	 * @param string $callback
	 * @return boolean true on success else return false
	 * @todo needs more documentation
	 * @internal
	 */
	public function build(PageBase $page, $callback=null){
		assert('is_null($callback) || is_string($callback)');

		$this->page = $page;
		$this->page->setCacheManager($this->cache);

		if(!is_null($callback)){
			$eval = '$this->page->'.$callback.';';
			eval($eval);
		} else {
			$this->page->build();
		}
		return true;
	}

	/**
	 * Get cache type.
	 *
	 * Get page caching type.
	 *
	 * @return integer cache type
	 */
	final protected function _getCacheType(){
		return $this->cache->getType();
	}

	/**
	 * Check if a page is cached.
	 *
	 * @return boolean true of cached else return false
	 */
	final protected function _isCached(){
		return $this->cache->isCached();
	}

	/**
	 * Set current active template.
	 *
	 * @uses PageFactoryTemplate::init()
	 * @uses PageFactoryTemplateEngine::$template
	 * @param PageFactoryTemplate $template
	 * @return boolean true on success else return false
	 * @todo needs more documentation
	 */
	public function setTemplate(PageFactoryTemplate $template){
		$this->template = $template;
		return $this->template->init();
	}

	/**
	 * Get current active template.
	 *
	 * @return PageFactoryTemplate
	 */
	public function getTemplate(){
		return $this->template;
	}

	/**
	 * Set cache manager.
	 *
	 * Set cache manager reference.
	 *
	 * @param CacheManager $cache
	 * @return void
	 * @internal
	 * @todo needs more documentation
	 */
	final public function setCacheManager(CacheManager $cache){
		$this->cache = $cache;
	}

	/**
	 * Draw page content.
	 *
	 * Render and draw page content and return a
	 * string ready for output or caching.
	 *
	 * @return string redered pages.
	 */
	abstract public function draw();

	/**
	 * Add page content to page.
	 *
	 * When the page build process is complete all
	 * {@link Output} class instances will be send
	 * to the template engine each {@link Output} content class
	 * will bee send to the template engine using this method.
	 *
	 * @param Output $content
	 * @return boolean true on success, else return false.
	 */
	abstract public function addPageContent(Output $content);

	/**
	 * Add page settings to page.
	 *
	 * When the page build process is complete all
	 * {@link Output} class instances will be send
	 * to the template engine each {@link Output} settings class
	 * will bee send to the template engine using this method.
	 *
	 * @param Output $content
	 * @return boolean true on success, else return false.
	 */
	abstract public function addPageSettings(Output $settings);
}


//*****************************************************************//
//*********************** PageFactory class ***********************//
//*****************************************************************//
/**
 * Page factory.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class PageFactory implements Singleton {


	//*****************************************************************//
	//***************** PageFactory class properties ******************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var PageFactory
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Current Page factory template engine.
	 *
	 * @var PageFactoryTemplateEngine
	 * @internal
	 */
	private $engine = null;

	/**
	 * Page build callback name.
	 *
	 * @var string method callback name
	 * @internal
	 */
	private $callback = null;

	/**
	 * Page resolvers
	 *
	 * array containing a list of available page resolvers
	 *
	 * @var array
	 * @see PageFactoryPageResolver
	 * @internal
	 */
	private $resolvers = array();

	/**
	 * Requested page url.
	 *
	 * @var string requested page url
	 * @internal
	 */
	private $url = null;

	/**
	 * Instance of cache manager
	 *
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;

	/**
	 * Write output to cache.
	 *
	 * @var boolean
	 * @internal
	 */
	private $write_to_cache = false;


	//*****************************************************************//
	//****************** PageFactory class methods ********************//
	//*****************************************************************//
	/**
	 * Create new instance of PageFactory.
	 *
	 * When a new instance is created the PageFactory will prepare
	 * the object and support objects like the toolbar for redering
	 * and the page url will be determined ready to beeing looked up.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			PageFactoryDeveloperToolbar::getInstance()->addItem(new PageFactoryDeveloperToolbarItemExectutionTimeCalculator());
		}

		$this->addResolver('meta', new PageFactoryResolverMeta());
		$this->addResolver('redirect', new PageFactoryResolverRedirect());

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

		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			PageFactoryDeveloperToolbar::getInstance()->addItem(new PageFactoryDeveloperToolbarItemCacheStatus($this->cache));
		}
	}

	/**
	 * Get PageFactory instance.
	 *
	 * Please refer to the {@link Singleton} interface for complete
	 * description.
	 *
	 * @see Singleton
	 * @uses PageFactory::$instance
	 * @return PageFactory
	 * @internal
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
		$eventHandler = EventHandler::getInstance();
		$eventHandler->trigger(new EventRequestStart());

		$page = PageFactory::getInstance();
 		$page->resolvePageController();

		if($page->getCacheType() != PAGE_FACTORY_CACHE_STATIC){
			if(class_exists(PAGE_FACTORY_CLASS_NAME, false)){
				eval('$page->build(new '.PAGE_FACTORY_CLASS_NAME.'());');
			} else {
				throw new BaseException('Could not find WebPage Class.');
			}
		}

		$eventHandler->trigger(new EventRequestEnd());

		$data = $page->draw().PageFactoryDeveloperToolbar::getInstance();

		if(Errorhandler::getInstance()->hasErrors()){
			echo Errorhandler::getInstance();
			$data = false;
		} else {
			if(!$return){
				echo $data;
				$data = true;
			}
		}
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
	 * @internal
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
	 * @todo uses documentation missing
	 * @internal
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
					include_once(PAGE_FACTORY_GET_FILE);
					$this->cache->setType(PAGE_FACTORY_CACHE_DYNAMIC);
					if(!$engine = $this->cache->getEngine()){
						$engine = PAGE_FACTORY_ENGINE;
					}
					$this->_enableEngine($engine);
					if($this->callback = $this->cache->getCallback()){
						return true;
					} else {
						include_once(PAGE_FACTORY_GET_FILE);
					}
				}
			} else {
				include_once(PAGE_FACTORY_GET_FILE);
			}
		}

		if(preg_match('/^\/corelib/', $this->url)){
			$manager = Manager::getInstance();
			$manager->init();
			$manager->setupPageRegistry($pages);
		} else if(preg_match('/^\/filesystem\/(.*?)\/$/', $this->url, $match)){
			Base::getInstance()->loadClass('FileSystemFile');
			$this->callback = 'getFile(\''.addcslashes($match[1], '\'').'\')';
			$this->page = FILE_SYSTEM_GET_FILE_HANDLER;
			$this->_enableEngine(FILE_SYSTEM_TEMPLATE_ENGINE);
			$this->cache->setType(PAGE_FACTORY_CACHE_DISABLED);
			require_once($this->page);
			return true;
		}


		if(isset($pages)){
			if(!$page = $this->_resolvePageController($pages)){
				$this->url = '/404/';
				if(!$page = $this->_resolvePageController($pages)){
					trigger_error('404 Error unspecified!', E_USER_ERROR);
					return false;
				} else {
					header('HTTP/1.1 404 Not Found');
				}
			}

			if($page){
				if(isset($page['precondition'])){
					if(!eval('return ('.$page['precondition'].');')){
						$this->url = '/403/';
						if(!$page = $this->_resolvePageController($pages)){
							trigger_error('403 Error unspecified!', E_USER_ERROR);
							return false;
						} else {
							header('HTTP/1.1 403 Forbidden');
						}
					}
				}
				if(!isset($page['page'])){
					throw new BaseException('file not set.', E_USER_ERROR);
				}
				if(!is_file($page['page'])){
					trigger_error('Unable to open: '.$page['page'].'. File not found.', E_USER_ERROR);
					return false;
				}
				if(!isset($page['engine'])){
					$page['engine'] = PAGE_FACTORY_ENGINE;
				}
				$this->callback = $page['exec'];
				$this->_enableEngine($page['engine']);
				$this->_setCacheSettings($page);
				require_once($page['page']);
			}
		}
		return true;
	}

	/**
	 * Resolve a url.
	 *
	 * @param array $pages
	 * @return mixed array containing page data, else return false.
	 */
	private function _resolvePageController(array &$pages){
		if(!isset($pages[$this->url])){
			foreach($pages as $val){
				if(is_array($val)){
					if(isset($val['type']) && $val['type'] != 'regex' ){
						$this->resolvers[$val['type']]->resolve($val['expr'], $val['exec'], $this->url);
						$val['expr'] = $this->resolvers[$val['type']]->getExpression();
						$val['exec'] = $this->resolvers[$val['type']]->getExecute();
					}
					if( isset($val['expr']) && $val['expr'] !== false && preg_match($val['expr'], $this->url) ){
						$val['exec'] = preg_replace($val['expr'], $val['exec'], addcslashes($this->url, '\''));
						return $val;
					}
				}
			}
			return false;
		}
		if(is_array($pages[$this->url])){
			if(!isset($pages[$this->url]['exec'])){
				$pages[$this->url]['exec'] = 'build';
			}
			$pages[$this->url]['exec'] .= '()';
			return $pages[$this->url];
		} else {
			return array('page' => $pages[$this->url], 'exec'=>'build()');
		}
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
	 * @internal
	 */
	public function getCacheType(){
		if(PAGE_FACTORY_CACHE_ENABLE && !PAGE_FACTORY_CACHE_DEBUG && $this->cache->isCached()){
			return $this->cache->getType();
		} else {
			return PAGE_FACTORY_CACHE_DYNAMIC;
		}
	}


	/**
	 * Set page caching settings.
	 *
	 * @param array $page page settings
	 * @return void
	 * @see CacheManager
	 * @internal
	 */
	private function _setCacheSettings(array $page){
		if(isset($page['cache'])){
			$this->cache->setPage($page['page'], $this->callback, $page['engine']);

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
	 * Enable alternate template engine.
	 *
	 * @param string $classname
	 * @return void
	 * @internal
	 */
	private function _enableEngine($classname){
		$engine = '$this->engine = new '.$classname.'();';
		eval($engine);
		assert('$this->engine instanceof PageFactoryTemplateEngine');
		$this->engine->setCacheManager($this->cache);
	}

	/**
	 * Template template engine to build page.
	 *
	 * @param PageBase $page
	 * @return boolean true on success else return false
	 * @internal
	 */
	public function build(PageBase $page){
		$page->setCacheManager($this->cache);
		$page->__init();
		$this->engine->build($page, $this->callback);
	}

	/**
	 * Draw page.
	 *
	 * Draw page and return or echo content. the draw
	 * process also coveres saving the page to the cache
	 * and reading it from the cache if page allready is
	 * cached.
	 *
	 * @return string
	 * @internal
	 */
	public function draw(){
		if($this->getCacheType() == PAGE_FACTORY_CACHE_STATIC && $this->cache->isCached()){
			return $this->cache->read();
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

			if(PAGE_FACTORY_CACHE_ENABLE && $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
				echo $this->cache->read();
				return true;
			}

			return $content;
		}

	}
}


//************************************************************************************//
//********** PageFactoryDeveloperToolbarItemExectutionTimeCalculator class ***********//
//************************************************************************************//
/**
 * Parsetime calculator toolbox item.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @internal
 */
class PageFactoryDeveloperToolbarItemExectutionTimeCalculator extends PageFactoryDeveloperToolbarItem {


	//************************************************************************************//
	//***** PageFactoryDeveloperToolbarItemExectutionTimeCalculator class properties *****//
	//************************************************************************************//
	/**
	 * @var float start microtime
	 */
	private $start = null;


	//************************************************************************************//
	//****** PageFactoryDeveloperToolbarItemExectutionTimeCalculator class methods *******//
	//************************************************************************************//

	/**
	 * Constructor.
	 *
	 * Assigns current {@link http://www.php.net/manual/en/function.microtime.php} to
	 * {@link PageFactoryDeveloperToolbarItemExectutionTimeCalculator::$start}
	 *
	 * @uses PageFactoryDeveloperToolbarItemExectutionTimeCalculator::$start
	 * @return void
	 */
	public function __construct(){
		$this->start = microtime(true);
	}

	/**
	 * Get toolbar item.
	 *
	 * Return html string containing a execution time icon and the actual execution time.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 */
	public function getToolbarItem(){
		return '<img src="corelib/resource/manager/images/icons/toolbar/parsetime.png" alt="parsetime" title="Page execution time"/> '.(round((microtime(true) - $this->start) , 4) * 1000).' ms.';
	}
}
?>