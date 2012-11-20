<?php
namespace Corelib\Base\PageFactory;

class Bootstrap {

	/**
	 * @var \Corelib\Routing\Registry|null
	 */
	private $registry = null;
	/**
	 * @var string|null
	 */
	private $url = null;

	public function __construct(\Corelib\Routing\Registry $registry){
		$this->registry = $registry;
		$this->_resolveURL();
	}

	public static function run(\Corelib\Routing\Registry $registry){
		if(!defined('BOOTSTRAP_DEVELOPER_TOOLBAR') || BOOTSTRAP_DEVELOPER_TOOLBAR == true){
			$toolbar = \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\PageFactory\Toolbar\Toolbar', true);
			$toolbar->addItem(new \Corelib\Base\PageFactory\Toolbar\Profiler());
			if(!defined('BOOTSTRAP_DEVELOPER_TOOLBAR') || BOOTSTRAP_DEVELOPER_TOOLBAR == true){
				\EventHandler::getInstance()->register(new Toolbar\Render($toolbar), 'Corelib\Base\PageFactory\Events\PageRender');
			}
		}

		$eventHandler = \EventHandler::getInstance();
		$eventHandler->trigger(new Events\RequestStart());

		$bootstrap = new self($registry);
		echo $bootstrap->render();

		$eventHandler->trigger(new Events\RequestEnd());
	}

	public function render(){
		if(\Corelib\Base\ServiceLocator\Locator::isLoaded('\Corelib\Base\Cache\Store')){
			$cache = \Corelib\Base\ServiceLocator\Locator::get('\Corelib\Base\Cache\Store');
			$cache_key = __CLASS__.':'.$_SERVER['SERVER_NAME'].':'.$_SERVER['REQUEST_URI'];
			if($cache->has($cache_key)){
				$page = $cache->get($cache_key);
			}
		}

		// Added new corelib path to registry
		if(substr($this->url, 0, 8) == '/corelib'){
			$pages = array();
			$manager = \Manager::getInstance();
			$manager->init();
			$manager->setupPageRegistry($pages);
			$this->registry->addRegistry(new \Corelib\Routing\ArrayRegistry($pages));
		}


		// If $page is not assigned, we asssume that the page was not cached and we will recreate it.
		if(!isset($page)){
			$route = $this->getRoute($this->url);
			$object = $this->getPage($route);
			$method = $route->getCallbackMethod();

			$result = call_user_func_array(array($object, $method), $route->getCallbackArgs());

			if($object->prepare() && $result !== false){
				$page = $object->render();
			}
		}
		if(\Errorhandler::getInstance()->hasErrors()){
			$page = \Errorhandler::getInstance()->draw();
		} else {
			// Check if $page still is unassigned, if not we assume that no output will be given
			if(isset($page)){
				if(isset($cache) && $route->getCache() && !$cache->has($cache_key)){
					$cache->store($cache_key, $page, $route->getCacheTTL());
				}
			} else {
				$page = '';
			}
		}
		if(!empty($page)){
			// If page has content trigger a PageRender Event allowing post processing of the page
			// $page is passed by reference, and should not be returned.
			\EventHandler::getInstance()->trigger(new Events\PageRender($page));
		}
		return $page;


	}

	public function getRoute($url){
		if($route = $this->registry->lookup($url)){
			return $route;
		} else if($route = $this->registry->lookup('/errors/404/')){
			return $route;
		} else {
			throw new \BaseException('404 Error unspecified!', E_USER_ERROR);
		}
	}

	public function getPage(\Corelib\Routing\Route $route){
		if($include = $route->getInclude()){
			include_once($include);
		}

		$object = $route->getCallbackClass();
		$object = new $object($route);

		if(is_callable(array($object, '__init'))){
			$object->__init();
		}
		return $object;
	}

	private function _resolveURL(){

		if(isset($_SERVER['SCRIPT_URL'])){
			$this->url = $_SERVER['SCRIPT_URL'];
 		} else {
			throw new \BaseException('$_SERVER[\'SCRIPT_URL\'] is not set, this is probably a bug in corelib, please report it along with a dump of you $_SERVER variable and the request url.', E_USER_ERROR);
		}

		/*
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
		*/
	}

}