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
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2006 Back in five minutes
 * @license http://www.bravura.dk/licence/corelib_1_0/
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
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
if(!defined('PAGE_FACTORY_CACHE_DIR')){
	define('PAGE_FACTORY_CACHE_DIR', 'var/cache/pages/');
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
			$eval = '$this->page->'.$callback.';';
			eval($eval);
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

	private $cache = array('type' => PAGE_FACTORY_CACHE_DISABLED);

	private $cache_file = null;

	private $write_to_cache = false;

	const TTL_FILE_SUFFIX = '.ttl';

	/**
	 * @return void
	 */
	private function __construct(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			PageFactoryDeveloperToolbar::getInstance()->addItem(new PageFactoryDeveloperToolbarItemParseTimeCalculator());
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
		$this->cache_file = PAGE_FACTORY_CACHE_DIR.str_replace('/', '_', $_SERVER['REQUEST_URI']);
	}

	/**
	 * Get PageFactory instance.
	 *
	 *	@return PageFactory
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
	 * @param $return boolean
	 * @return mixed string or boolean
	 */
	public static function bootstrap($return=false){
		$eventHandler = EventHandler::getInstance();
		$eventHandler->triggerEvent(new EventRequestStart());

		$page = PageFactory::getInstance();
		$page->resolvePageObject();

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

		$data = false;
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

		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			echo PageFactoryDeveloperToolbar::getInstance();
		}
		return $data;
	}

	/**
	 * @param string $ident
	 * @param PageFactoryPageResolver $resolver
	 * @return PageFactoryPageResolver on success else return boolean false
	 */
	public function addResolver($ident, PageFactoryPageResolver $resolver){
		try {
			StrictTypes::isString($ident);
		} catch (BaseException $e){
			echo $e;
		}
		$this->resolvers[$ident] = $resolver;
		return $resolver;
	}

	public function resolvePageObject(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			include_once(PAGE_FACTORY_POST_FILE);
		} else {
			if(PAGE_FACTORY_CACHE_ENABLE && is_file($this->cache_file)){
				if(is_file($this->cache_file.self::TTL_FILE_SUFFIX)){
					$ttl = (int) file_get_contents($this->cache_file.self::TTL_FILE_SUFFIX);

					if(time() > (filemtime($this->cache_file) + $ttl)){
						unlink($this->cache_file);
					}
				}

				if(is_file($this->cache_file)){
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

	public function getCacheType(){
		return $this->cache['type'];
	}


	/**
	 * @param array $page
	 * @return void
	 */
	private function _setCacheSettings(array $page){
		if(isset($page['cache']) && $page['cache'] == PAGE_FACTORY_CACHE_STATIC){
			if(!is_dir(dirname($this->cache_file))){
				mkdir(dirname($this->cache_file), 0777, true);
			}
			$this->write_to_cache = true;
			if(isset($page['ttl'])){
				file_put_contents($this->cache_file.self::TTL_FILE_SUFFIX, $page['ttl']);
			}
		}
	}

	/**
	 * @param PageBase $page
	 * @return void
	 */
	public function build(PageBase $page){
		$this->engine->build($page, $this->callback);
	}

	/**
	 * @param boolean $return
	 * @return mixed
	 */
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
			if($this->write_to_cache){
				file_put_contents($this->cache_file, $content);
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
class PageFactoryDeveloperToolbarItemParseTimeCalculator extends PageFactoryDeveloperToolbarItem {
	private $start = null;

	public function __construct(){
		$this->start = microtime(true);
	}

	public function getToolbarItem(){
		return '<img src="corelib/resource/manager/images/page/icons/toolbar/parsetime.png" alt="parsetime" title="Page parsetime"/> '.round((microtime(true) - $this->start), 4).' s.';
	}

	public function getContent(){
		return false;
	}
}
?>