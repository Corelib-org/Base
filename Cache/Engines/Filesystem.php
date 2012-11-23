<?php
namespace Corelib\Base\Cache\Engines;

use Corelib\Base\Cache\Engine;

class Filesystem implements Engine {

	private $directory=null;

	public function __construct($directory=BASE_CACHE_DIRECTORY){
		$this->directory = $directory;
	}

	public function has($key){
		$filename = $this->directory.'/'.$key;
		if(is_file($filename) && filemtime($filename) > time()){
			return true;
		}
		return false;
	}

	public function store($key, $value, $lifetime=null){
		$filename = $this->directory.'/'.$key;
		file_put_contents($filename, $value);
		if(!is_null($lifetime)){
			touch($filename, time()+$lifetime);
		}
	}

	public function getLocation($key){
		return $filename = $this->directory.'/'.$key;
	}

}


?>