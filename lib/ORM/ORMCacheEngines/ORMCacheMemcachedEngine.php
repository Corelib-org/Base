<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * ORM Cache memcached engine
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
//*************** ORMCacheMemcachedEngine class *******************//
//*****************************************************************//
/**
 * ORM Cache memcache dengine.
 *
 * This caching engine allows use of pecl-memcache extension.
 *
 * @category corelib
 * @package Base
 * @subpackage ORM
 * @see ORMCacheEngine
 * @see ORMCache
 */
class ORMCacheMemcachedEngine implements ORMCacheEngine {


	//*****************************************************************//
	//********** ORMCacheMemcachedEngine class properties *************//
	//*****************************************************************//
	/**
	 * Memcached instance.
	 *
	 * @var Memcached
	 */
	private $memcached = null;

	/**
	 * Object timeout.
	 *
	 * @integer timeout in seconds
	 */
	private $timeout = 2592000;


	//*****************************************************************//
	//************* ORMCacheMemcachedEngine class methods *************//
	//*****************************************************************//
	/**
	 * ORMCacheMemcachedEngine constructor.
	 *
	 * @param string $prefix object key prefix
	 * @return void
	 * @uses ORMCacheMemcachedEngine::$memcached
	 */
	public function __construct($prefix=''){
		$this->memcached = new Memcached();
		$this->memcached->setOption(Memcached::OPT_PREFIX_KEY, $prefix.__CLASS__);
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
	}

	/**
	 * Add memcache server to server pool.
	 *
	 * @param string $hostname Server hostname
	 * @param integer $port Server port
	 * @param integer $weight Server weight
	 */
	public function addServer($hostname, $port=11211, $weight=0){
		$this->memcached->addServer($hostname, $port, $weight);
	}

	/**
	 * Get object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function getInstance($key){
		if($object = $this->memcached->get($key)){
			return $object;
		} else if($this->memcached->getResultCode() != Memcached::RES_NOTFOUND){
			throw new BaseException('Unable to get cached class instance, memcache error: '.$this->memcached->getResultCode(), E_USER_ERROR);
		}
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
		if($this->memcached->set($key, $instance, $this->timeout)){
			return true;
		} else if($this->memcached->getResultCode() != Memcached::RES_SUCCESS){
			throw new BaseException('Unable to store class instance, memcache error: '.$this->memcached->getResultCode(), E_USER_ERROR);
		}
	}

	/**
	 * Remove object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function removeInstance($key){
		if($this->memcached->delete($key)){
			return false;
		} else if($this->memcached->getResultCode() != Memcached::RES_NOTFOUND){
			throw new BaseException('Unable to remove cached class instance, memcache error: '.$this->memcached->getResultCode(), E_USER_ERROR);
		}
	}
}
?>