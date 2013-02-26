<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib dummy website abstracts file
 *
 * <i>No Description</i>
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package Dummy
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */
use Corelib\Base\ServiceLocator\Locator;


$loader = Locator::get('Corelib\Base\Core\Loader');
$loader->addClassPath('lib/');


// Base::getInstance()->addClassPath('lib/');

define('SOFTWARE_VERSION', 'Corelib Dummy Site v1.0.0');

if(!defined('DAILY_DIGEST')){
	define('DAILY_DIGEST', 'ss@gormlarsenzornig.com');
}


//*****************************************************************//
//**************** Abstract dummy help contants *******************//
//*****************************************************************//

// Check to see if ABSTRACTS_ENABLE_AUTHORIZATION is set true
// If set to true the UserAuthorization classes is automatically loaded
if(defined('ABSTRACTS_ENABLE_AUTHORIZATION') && ABSTRACTS_ENABLE_AUTHORIZATION){
	$base->loadClass('UserAuthorization');
} else if(!defined('ABSTRACTS_ENABLE_AUTHORIZATION')){
	/**
	 * Enable autoloading of UserAuthorization features.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('ABSTRACTS_ENABLE_AUTHORIZATION', false);
}


// Check to see if ABSTRACTS_ENABLE_DATABASE is set true
// If set to true the Database classes is automatically loaded
if(defined('ABSTRACTS_ENABLE_DATABASE') && ABSTRACTS_ENABLE_DATABASE){


	$masterdb = new Corelib\Base\Database\MySQLi\Engine(DATABASE_MASTER_HOSTNAME,
	                                                    DATABASE_MASTER_USERNAME,
	                                                    DATABASE_MASTER_PASSWORD,
	                                                    DATABASE_MASTER_DATABASE);
	$conn = new Corelib\Base\Database\Connection($masterdb);
	Corelib\Base\ServiceLocator\Locator::load($conn);


} else if(!defined('ABSTRACTS_ENABLE_DATABASE')){
	/**
	 * Enable autoloading of Database features.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('ABSTRACTS_ENABLE_DATABASE', false);
}

// Check to see if ABSTRACTS_ENABLE_ORM_CACHE is set true
// If set to true the ORM caching classes is automatically loaded
if(defined('ABSTRACTS_ENABLE_ORM_CACHE') && ABSTRACTS_ENABLE_ORM_CACHE){
	$engine = ORMCache::setEngine(new ORMCacheMemcacheEngine());
	$engine->addServer('localhost');
} else if(!defined('ABSTRACTS_ENABLE_ORM_CACHE')){
	/**
	 * Enable autoloading of ORM caching features.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('ABSTRACTS_ENABLE_ORM_CACHE', false);
}

$cache = new \Corelib\Base\Cache\Store(new \Corelib\Base\Cache\Engines\Filesystem());
Locator::load($cache);

\Corelib\Base\Log\Logger::setLevel(\Corelib\Base\Log\Logger::ALL);
?>