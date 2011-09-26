<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * ORM Cache
 *
 * The ORM cache allows for ORM object to be cached in memory, and increasing
 * performance of the systemet. and releaving the database from unessacery queries.
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
//****************** ORMCacheEngine interface *********************//
//*****************************************************************//
/**
 * ORM Cache engine interface.
 *
 * This interfaces allows developer to implement their own object caching
 * engine.
 *
 * @category corelib
 * @package Base
 * @subpackage ORM
 * @see ORMCacheMemcacheEngine
 * @see ORMCacheMemcachedEngine
 */
interface ORMCacheEngine {


	//*****************************************************************//
	//************** ORMCacheEngine interface methods *****************//
	//*****************************************************************//
	/**
	 * Get object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 */
	public function getInstance($key);

	/**
	 * Store object instance in cache.
	 *
	 * @param string $key object key
	 * @param object $instance object instance
	 * @return boolean true on success, else return false
	 */
	public function storeInstance($key, $instance);

	/**
	 * Remove object instance from cache.
	 *
	 * @param string $key object key
	 * @return boolean true on success, else return false
	 */
	public function removeInstance($key);
}


//*****************************************************************//
//******************* ORMRelationHelper class *********************//
//*****************************************************************//
/**
 * ORM cache class.
 *
 * This object allows ORM classes to be stored in cache. normally
 * the caching features for each orm class is automatically implemented
 * in orm classes created using the corelib code generator.
 *
 * @category corelib
 * @package Base
 * @subpackage ORM
 */
class ORMCache {


	//*****************************************************************//
	//******************* ORMCache class properties *******************//
	//*****************************************************************//
	/**
	 * Active engine reference.
	 *
	 * @var ORMCacheEngine
	 */
	private static $engine = false;


	//*****************************************************************//
	//******************** ORMCache class methods *********************//
	//*****************************************************************//
	/**
	 * Set object caching engine.
	 *
	 * @param ORMCacheEngine $engine object caching engine
	 * @return boolean true on success, else return false
	 * @see ORMCacheMemcacheEngine
	 * @see ORMCacheMemcachedEngine
	 */
	public static function setEngine(ORMCacheEngine $engine){
		return self::$engine = $engine;
	}

	/**
	 * Get object instance from cache.
	 *
	 * Retrieve object instance from cache. If no caching engine
	 * have been enebled this method will always return false.
	 *
	 * @param string $classname class name
	 * @param string $method class method
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public static function getInstance($classname, $method){
		if(self::$engine){
			return self::$engine->getInstance(call_user_func_array(array(__CLASS__, '_makeKey'), func_get_args()));
		} else {
			return false;
		}
	}

	/**
	 * Remove object instance from cache.
	 *
	 * Remove object instance from cache. If no caching engine
	 * have been enebled this method will always return false.
	 *
	 * @param string $classname class name
	 * @param string $method class method
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public static function removeInstance($classname, $method){
		if(self::$engine){
			return self::$engine->removeInstance(call_user_func_array(array(__CLASS__, '_makeKey'), func_get_args()));
		} else {
			return true;
		}
	}

	/**
	 * Store object instance from cache.
	 *
	 * Store object instance from cache. If no caching engine
	 * have been enebled this method will always return false.
	 *
	 * @param string $classname class name
	 * @param string $method class method
	 * @param object $object object instance
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public static function storeInstance($classname, $method, $object){
		if(self::$engine){
			$args = func_get_args();
			unset($args[2]);
			$key = call_user_func_array(array(__CLASS__, '_makeKey'), $args);
			return self::$engine->storeInstance($key, $object);
		} else {
			return false;
		}
	}

	/**
	 * Make object instance identification key.
	 *
	 * @return string object identification key
	 */
	private static function _makeKey($classname, $function=null){
		return implode('/', func_get_args());
	}
}
?>