<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib service locator.
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
 * @author Steffen Sørensen <steffen@sublife.dk>
 * @copyright Copyright (c) 2012 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version ($Id$)
 * @see Corelib\Base\ServiceLocator\Locator
 */
namespace Corelib\Base\ServiceLocator;


/**
 * Corelib service locator.
 *
 * The service locator is a new and improved version of all
 * the Singleton classes in Corelib. The Service Locator is
 * suppose to replace all Singleton classes, and making
 * corelib easier to test.
 *
 * <code>
 * // Load service
 * Corelib\Base\ServiceLocator\Locator::load(new MyService);
 *
 * // Unloading a service
 * Corelib\Base\ServiceLocator\Locator::unload('MyService');
 *
 * // Getting a service instance:
 * Corelib\Base\ServiceLocator\Locator::get('MyService');
 *
 * // Check if a class is loaded and load a new one:
 * if(!Corelib\Base\ServiceLocator\Locator::isLoaded('MyService')){
 *     Corelib\Base\ServiceLocator\Locator::load(new MyService);
 * }
 *
 * // In some cases, it desireable to load a mock service in a test
 * // invironment, this can be done by overloading the class identifier:
 * Corelib\Base\ServiceLocator\Locator::load(new MyTestService, 'MyService');
 * Corelib\Base\ServiceLocator\Locator::get('MyService') // returns MyTestService
 * </code>
 *
 * @package Corelib\Base
 * @api
 */
class Locator {

	/**
	 * Loaded services.
	 *
	 * Reference array which contains a list of all loaded services
	 *
	 * @var array
	 * @internal
	 */
	private static $services = array();

	/**
	 * Declare object constructor private.
	 *
	 * This object is a static class and is therefore
	 * not allowed to be instanciated.
	 *
	 * @internal
	 */
	private function __construct(){ }

	/**
	 * Load a new service.
	 *
	 * @param Service $service
	 * @param $class
	 * @api
	 */
	public static function load(Service $service, $class=null){
		if(is_null($class)){
			$class = get_class($service);
		}
		assert('is_string($class)');
		self::$services[$class] = $service;
	}

	/**
	 * Get service by it's class name.
	 *
	 * @param string $class
	 * @return Service
	 * @throws Exception
	 * @api
	 */
	public static function get($class){
		assert('is_string($class)');
		if(!isset(self::$services[$class])){
			throw new Exception('Requested service \''.$class.'\', has not been loaded');
		} else {
			return self::$services[$class];
		}
	}

	/**
	 * Unload a service by it's class name.
	 *
	 * @param string $class
	 * @return bool true on success
	 * @throws Exception if service doesn't exists
	 * @api
	 */
	public function unload($class){
		if(self::get($class)){
			unset(self::$services[$class]);
			return true;
		}
	}

	/**
	 * Check if service is loaded.
	 *
	 * @param string $class class name
	 * @return bool
	 * @api
	 */
	public function isLoaded($class){
		return isset(self::$services[$class]);
	}

	/**
	 * Create a printout of services loaded.
	 *
	 * @param bool $verbose if true, dump all service data.
	 * @return void
	 */
	public static function debug($verbose=false){
		if($verbose){
			print_r(self::$services);
		} else {
			$data = array();
			foreach(self::$services as $key => $val){
				$data[$key] = $val;
			}
			print_r($data);
		}
	}
}
?>