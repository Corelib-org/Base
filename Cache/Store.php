<?php
namespace Corelib\Base\Cache;
use Corelib\Base\ServiceLocator\Service;

class Store implements Service {
	private $engine = null;

	public function __construct(Engine $engine = null){
		$this->engine = $engine;
	}

	public static function getInstance() {
		return \Corelib\Base\ServiceLocator\Locator::get(__CLASS__);
	}

	public function store($key, $value, $lifetime=36000){
		if(!is_null($this->engine)){
			return $this->engine->store($this->_hash($key), $value, $lifetime);
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
			return $this->engine->has($this->_hash($key));
		}
		return false;
	}

	public function purge($key){
		if(!is_null($this->engine)){
			return $this->engine->purge($key);
		}
		return true;
	}

	public function getLocation($key){
		if(!is_null($this->engine)){
			return $this->engine->getLocation($this->_hash($key));
		}
		return true;
	}

	private function _hash($key){
		return md5($key);
	}
}
?>