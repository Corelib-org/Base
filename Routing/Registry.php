<?php
namespace Corelib\Base\Routing;
use Corelib\Base\Log\Logger, stdClass, Corelib\Base\ServiceLocator\Locator;

class Registry {

	private $routes = array();
	private $resolvers = array();
	private $error_prefix = '/errors/';

	private $cache_key = null;
	private $cache_update = false;
	private $cached = false;
	/**
	 * @var \Corelib\Base\Cache\Store
	 */
	private $cache_store = null;

	public function __construct($cache_key){
		$this->cache_key = get_class($this).':'.$cache_key;

		if(Locator::isLoaded('Corelib\Base\Cache\Store')){
			$this->cache_store = Locator::get('Corelib\Base\Cache\Store');
			if(!$this->cache_store->has($this->cache_key)){
				$this->cache_update = true;
			} else {
				$location = $this->cache_store->getLocation($this->cache_key);
				include($location);
				$this->cached = true;
			}
		}
	}

	public function isCached(){
		return $this->cached;
	}

	public function addRoute(Route $route){
		if($prefix = $route->getPrefix()){
			if($route->getURL()){
				$this->routes[$prefix]['route'] = $route;
			} else {
				$this->routes[$prefix]['patterns'][] = $route;
			}
		} else {
			$this->routes['#patterns'][] = $route;
		}
	}

	public function addRegistry(Registry $registry){
		while(list($key,$data) = each($registry->routes)){


			if($data instanceof Route){
				$this->addRoute($data);
			} else if(isset($data['route'])){
				$this->addRoute($data['route']);
			} else if(is_array($data)){
				while(list(,$pattern) = each($data)){
					$this->addRoute($pattern);
				}
			}
			if(isset($data['patterns']) && is_array($data['patterns'])){
				while(list(,$pattern) = each($data['patterns'])){
					$this->addRoute(new Route($pattern));
				}
			}
		}
	}

	public function lookup($uri){
		// Look for a direct match first
		Logger::info('Looking up uri: '.$uri);
		if(isset($this->routes[$uri]['route'])){
			return $this->routes[$uri]['route'];
		}
		if(substr($uri, -1)){
			$uri_parts = substr($uri, 0, -1);
		}

		$uri_parts = explode('/', $uri_parts);
		while(sizeof($uri_parts) > 0){
			$part = implode('/', $uri_parts).'/';

			if(isset($this->routes[$part]['patterns']) && is_array($this->routes[$part]['patterns'])){
				if($route = $this->_lookup($uri, $this->routes[$part]['patterns'])){
					return $route;
				}
			}
			array_pop($uri_parts);
		}

		if(isset($this->routes['#patterns']) && is_array($this->routes['#patterns'])){
			if($route = $this->_lookup($uri, $this->routes['#patterns'])){
				return $route;
			}
		}
		return false;
	}

	private function _lookup($uri, array &$lookup){
		foreach($lookup as $key => $val){
			if(preg_match($val->getExpression(), $uri, $matches)){
				$val->parseMacros($matches);
				return $val;
			}
		}
		return false;
	}

	public function __destruct(){
		if($this->cache_update){
			if(Locator::isLoaded('Corelib\Base\Cache\Store')){
				$cache = Locator::get('Corelib\Base\Cache\Store');
				$cache_key = $this->cache_key;
				$cache->store($cache_key, '<?php $this->routes = '.var_export($this->routes, true).'; ?>');
			}
		}
	}
}
?>