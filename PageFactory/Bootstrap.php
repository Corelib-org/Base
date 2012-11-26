<?php
namespace Corelib\Base\PageFactory;

use Corelib\Base\Routing\Registry, Corelib\Base\Routing\ArrayRegistry;
use Corelib\Base\Routing\Route;
use Corelib\Base\ServiceLocator\Locator;
use Corelib\Base\PageFactory\Toolbar\Profiler;
use Corelib\Base\ErrorHandler;
use Corelib\Base\Log\Logger;
use Corelib\Base\Core\Exception;


class Bootstrap {

	/**
	 * @var \Corelib\Routing\Registry|null
	 */
	private $registry = null;
	/**
	 * @var string|null
	 */
	private $url = null;

	public function __construct(Registry $registry){
		$this->registry = $registry;
		$this->_resolveURL();
	}

	public static function run(Registry $registry){
		$event_handler = Locator::get('Corelib\Base\Event\Handler');
		if(!defined('BOOTSTRAP_DEVELOPER_TOOLBAR') || BOOTSTRAP_DEVELOPER_TOOLBAR == true){
			$toolbar = Locator::get('Corelib\Base\PageFactory\Toolbar\Toolbar', true);
			$toolbar->addItem(new Profiler());
			if(!defined('BOOTSTRAP_DEVELOPER_TOOLBAR') || BOOTSTRAP_DEVELOPER_TOOLBAR == true){
				$event_handler->register(new Toolbar\Render($toolbar), 'Corelib\Base\PageFactory\Events\PageRender');
			}
		}

		$event_handler->trigger(new Events\RequestStart());

		$bootstrap = new self($registry);
		echo $bootstrap->render();

		$event_handler->trigger(new Events\RequestEnd());
	}

	public function render(){
		if(Locator::isLoaded('Corelib\Base\Cache\Store')){
			$cache = Locator::get('Corelib\Base\Cache\Store');
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
			$this->registry->addRegistry(new ArrayRegistry(CURRENT_WORKING_DIR.'Manager::setupPageRegistry', $pages));
		}


		// If $page is not assigned, we asssume that the page was not cached and we will recreate it.
		if(!isset($page)){
			$route = $this->getRoute($this->url);
			if($precondition = $route->getCallbackCondition()){
				if($precondition == 'eval'){
					$args = $route->getCallbackConditionArgs();
					$result = eval('return '.$args[0].';');
				} else {
					throw new Exception('Precondition engine is not implemented yet');
				}
				if(!$result){
					$route = $this->getRoute('/403/');
				}
			}

			$object = $this->getPage($route);
			$method = $route->getCallbackMethod();

			$result = call_user_func_array(array($object, $method), $route->getCallbackArgs());

			if($object->prepare() && $result !== false){
				Logger::info('Template prepared');
				$page = $object->render();
				Logger::info('Template rendered');
			}
		}
		if(Locator::isLoaded('Corelib\Base\ErrorHandler')){
			$error_handler = Locator::get('Corelib\Base\ErrorHandler');
		}
		if(isset($error_handler) && $error_handler->hasErrors()){
			$page = $error_handler->draw();
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
			Locator::get('Corelib\Base\Event\Handler')->trigger(new Events\PageRender($page));
		}
		return $page;


	}

	public function getRoute($url){
		if($route = $this->registry->lookup($url)){
			return $route;
		} else if($route = $this->registry->lookup('/errors/404/')){
			return $route;
		} else {
			throw new Exception('404 Error unspecified!', E_USER_ERROR);
		}
	}

	public function getPage(Route $route){
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
		} else if(isset($_SERVER['REQUEST_URI'])){
			list($this->url) = explode('?', $_SERVER['REQUEST_URI']);
 		} else {
			throw new Exception('$_SERVER[\'SCRIPT_URL\'] is not set, this is probably a bug in corelib, please report it along with a dump of you $_SERVER variable and the request url.', E_USER_ERROR);
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