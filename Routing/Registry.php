<?php
namespace Corelib\Base\Routing;

class Registry {

	private $routes = array();
	private $resolvers = array();
	private $error_prefix = '/errors/';

	public function __construct(){

	}

	public function addRoute(Route $route){
		$raw_route = new \stdClass;

		if($url = $route->getUrl()){
			$raw_route->url = $url;
		}
		if($expression = $route->getExpression()){
			$raw_route->expression = $expression;
		}
		if($callback_class = $route->getCallbackClass()){
			$raw_route->callback_class = $callback_class;
		}
		if($callback_method = $route->getCallbackMethod()){
			$raw_route->callback_method = $callback_method;
		}
		if($callback_args = $route->getCallbackArgs()){
			$raw_route->callback_args = $callback_args;
		}
		if($callback_condition = $route->getCallbackCondition()){
			$raw_route->callback_condition = $callback_condition;
		}
		if($callback_condition_args = $route->getCallbackConditionArgs()){
			$raw_route->callback_condition_args = $callback_condition_args;
		}
		if($include = $route->getInclude()){
			$raw_route->include = $include;
		}

		if($prefix = $route->getPrefix()){
			$raw_route->prefix = $prefix;
			if(isset($raw_route->url)){
				$this->routes[$prefix]['route'] = $raw_route;
			} else {
				$this->routes[$prefix]['patterns'][] = $raw_route;
			}
		} else {
			$this->routes['#patterns'][] = $raw_route;
		}
	}

	public function addRegistry(Registry $registry){
		while(list($key,$data) = each($registry->routes)){
			if($data instanceof stdClass){
				$this->addRoute($data);
			} else if(isset($data['route'])){
				$this->addRoute(new Route($data['route']));
			} else if(is_array($data)){
				while(list(,$pattern) = each($data)){
					$this->addRoute(new Route($pattern));
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
		if(isset($this->routes[$uri]['route'])){
			return new Route($this->routes[$uri]['route']);
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
			if(preg_match($val->expression, $uri, $matches)){
				$val->url = $uri;
				return new Route($val, $matches);
			}
		}
		return false;
	}
}
?>