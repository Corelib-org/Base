<?php
use Corelib\Base\Core\ErrorHandler, Corelib\Base\ServiceLocator\Locator;

/**
 *	Define Base Development Runlevel Constant.
 */
define('BASE_RUNLEVEL_DEVEL', 2);

/**
 *	Define Base Production Runlevel Constant.
 */
define('BASE_RUNLEVEL_PROD', 1);

/**
 *	Define current version of corelib Base.
 */
define('CORELIB_BASE_VERSION', '6.0.0');

if(!defined('CORELIB')){
	/**
	 * 	Corelib Path Constant.
	 *
	 * 	This constanst holds the path to the corelib
	 */
	define('CORELIB', realpath(dirname(__FILE__).'/../').'/');
}

if(!defined('BASE_RUNLEVEL')){
	/**
	 * Current Runlevel.
	 *
	 * This constant holds the current runlevel
	 */
	define('BASE_RUNLEVEL', BASE_RUNLEVEL_DEVEL);
}

if(!defined('BASE_DEFAULT_TIMEZONE')){
	/**
	 * Define default timezone.
	 *
	 * Define the default timezone for use in php date functions
	 */
	define('BASE_DEFAULT_TIMEZONE', 'CET');
}

if (!defined('BASE_CACHE_DIRECTORY')){
	/**
	 * Define cache directory.
	 *
	 * This constants holds the path, on where to store the cached files
	 * this directory must be writable by the user running
	 * the script, and it can be overwritten any time before include
	 * Base.php .
	 *
	 * @see Base.php
	 */
	define('BASE_CACHE_DIRECTORY', 'var/cache/');
}

if(!defined('CURRENT_WORKING_DIR')){
/**
 *	Current Working Dir Constant.
 *
 * 	This constant holds the path to the current working dir
 */
	define('CURRENT_WORKING_DIR', getcwd().'/');
}

if(is_callable('mb_internal_encoding')){
	mb_internal_encoding('utf-8');
}
if(is_callable('date_default_timezone_set')){
	date_default_timezone_set(BASE_DEFAULT_TIMEZONE);
}

require_once(CORELIB.'/Base/ServiceLocator/Locator.php');
require_once(CORELIB.'/Base/ServiceLocator/Service.php');
require_once(CORELIB.'/Base/ServiceLocator/Autoloadable.php');

require_once(CORELIB.'/Base/Core/Loader.php');
require_once(CORELIB.'/Base/Log/Logger.php');

Locator::get('Corelib\Base\Core\Loader');


if(php_sapi_name() == 'cli' && (!defined('BASE_SUPPRESS_CLI_HEADER') || BASE_SUPPRESS_CLI_HEADER !== true)){
	fputs(STDOUT, 'Corelib v'.CORELIB_BASE_VERSION.' - http://www.corelib.org'."\n\0");

	require_once(CORELIB.'/Base/Log/Engine.php');
	require_once(CORELIB.'/Base/Log/Engines/File.php');
	require_once(CORELIB.'/Base/Log/Engines/Stdout.php');

	Logger::setEngine(new LoggerEngineStdout());
} else {
	header('X-Powered-By: Corelib v'.CORELIB_BASE_VERSION.' - http://www.corelib.org');
}





if(!defined('BASE_DISABLE_ERROR_HANDLER') || BASE_DISABLE_ERROR_HANDLER === false){
	require_once(CORELIB.'/Base/ServiceLocator/Locator.php');
	require_once(CORELIB.'/Base/ServiceLocator/Service.php');
	require_once(CORELIB.'/Base/Core/ErrorHandler.php');


	$error_handler = Locator::load(new ErrorHandler());


	if(BASE_RUNLEVEL > BASE_RUNLEVEL_PROD){
		assert_options(ASSERT_ACTIVE, true);
		assert_options(ASSERT_BAIL, false);
		assert_options(ASSERT_WARNING, false);
		assert_options(ASSERT_CALLBACK, array($error_handler, 'assert'));
	} else {
		assert_options(ASSERT_ACTIVE, false);
	}
	ini_set('html_errors',false);
	ini_set('error_prepend_string','Corelib-ErrorHandler-'.time());
	set_error_handler(array($error_handler, 'trigger'));
	ob_start(array($error_handler, 'fatal'));
	//register_shutdown_function(array(ErrorHandler::getInstance(), 'fatal'));
}

