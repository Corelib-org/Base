<?php
namespace Corelib\Base\Cache;
use Corelib\Base\ServiceLocator\Service;

class Store implements Service {
	private $engine = null;

	public function __construct(Engine $engine = null){
		$this->engine = $engine;
	}

	public function store($key, $value, $lifetime=null){
		if(!is_null($this->engine)){
			return $this->engine->store($key, $value, $lifetime);
		}
		return true;
	}

	public function get($key){
		if(!is_null($this->engine)){
			return $this->engine->get($key);
		}
		return false;
	}

	public function has($key){
		if(!is_null($this->engine)){
			return $this->engine->has($key);
		}
		return false;
	}

	public function purge($key){
		if(!is_null($this->engine)){
			return $this->engine->purge($key);
		}
		return true;
	}
}
?>