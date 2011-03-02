<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * ORM Cache memcache engine
 *
 * <i>No description</i>
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
 * @subpackage ORM
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//**************** ORMCacheMemcacheEngine class *******************//
//*****************************************************************//
/**
 * ORM Cache memcache engine.
 *
 * This caching engine allows use of pecl-memcache extension.
 *
 * @category corelib
 * @package Base
 * @subpackage ORM
 * @see ORMCacheEngine
 * @see ORMCache
 */
class ORMCacheMemcacheEngine implements ORMCacheEngine {


	//*****************************************************************//
	//*********** ORMCacheMemcacheEngine class properties *************//
	//*****************************************************************//
	/**
	 * Memcache instance.
	 *
	 * @var Memcache
	 */
	private $memcache = null;

	/**
	 * Object timeout.
	 *
	 * @integer timeout in seconds
	 */
	private $timeout = 2592000;

	/**
	 * Object key prefix.
	 *
	 * @string prefix
	 */
	private $prefix = '';


	//*****************************************************************//
	//************* ORMCacheMemcacheEngine class methods **************//
	//*****************************************************************//
	/**
	 * ORMCacheMemcacheEngine constructor.
	 *
	 * @param string $prefix object key prefix
	 * @return void
	 * @uses ORMCacheMemcacheEngine::$memcache
	 * @uses ORMCacheMemcacheEngine::$prefix
	 */
	public function __construct($prefix=''){
		$this->memcache = new Memcache();
		$this->prefix = $prefix.__CLASS__;
	}

	/**
	 * Set object timeout.
	 *
	 * Set a object timeout, default is 30 days
	 *
	 * @param integer $timeout timeout in seconds
	 * @return boolean true on success, else return false
	 */
	public function setTimeout($timeout){
		$this->timeout = $timeout;
		return true;
	}

	/**
	 * Add memcache server to server pool.
	 *
	 * @param string $hostname Server hostname
	 * @param integer $port Server port
	 * @param integer $weight Server weight
	 */
	public function addServer($hostname, $port=11211, $weight=0){
		return $this->memcache->addServer($hostname, $port, $weight);
	}

	/**
	 * Get object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function getInstance($key){
		return $this->memcache->get($this->prefix.$key);
	}

	/**
	 * Store object instance in cache.
	 *
	 * @param string $key object key
	 * @param object $instance object to store
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function storeInstance($key, $instance){
		return $this->memcache->set($this->prefix.$key, $instance, null, $this->timeout);
	}

	/**
	 * Remove object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function removeInstance($key){
		return $this->memcache->delete($this->prefix.$key);
	}

}
