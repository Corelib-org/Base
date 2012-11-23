<?php
namespace Corelib\Base\Cache;


interface Engine {
	public function has($key);
	public function store($key, $value, $lifetime=null);
	public function getLocation($key);
}
?>