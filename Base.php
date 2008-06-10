<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base Functions and Classes
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
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 * @filesource
 */

//*****************************************************************//
//************************ Table Of Content ***********************//
//*****************************************************************//
//**                                                             **//
//**    1. Define Contants .................................     **//
//**        1. BASE_RUNLEVEL_TERM_DEBUG ....................     **//
//**        2. BASE_RUNLEVEL_TERM_NOTICE ...................     **//
//**        3. BASE_RUNLEVEL_TERM_WARN .....................     **//
//**        4. BASE_RUNLEVEL_DEVEL .........................     **//
//**        5. BASE_RUNLEVEL_PROD ..........................     **//
//**        6. CORELIB_BASE_VERSION ........................     **//
//**        7. CORELIB_BASE_VERSION_MAJOR ..................     **//
//**        8. CORELIB_BASE_VERSION_MINOR ..................     **//
//**        9. CORELIB_BASE_VERSION_PATCH ..................     **//
//**       10. CORELIB_COPYRIGHT ...........................     **//
//**       11. CORELIB_COPYRIGHT_YEAR ......................     **//
//**    2. Basic Configuration Checks ......................     **//
//**        1. CORELIB .....................................     **//
//**        2. CURRENT_WORKING_DIR .........................     **//
//**        3. BASE_RUNLEVEL ...............................     **//
//**        4. BASE_CLASS_CACHE_FILE .......................     **//
//**        5. BASE_DEFAULT_TIMEZONE .......................     **//
//**        6. BASE_ADMIN_EMAIL ............................     **//
//**        7. TEMPORARY_DIR ...............................     **//
//**    3. Load Base Support Files .........................     **//
//**        1. Base/Lib/Interfaces.php .....................     **//
//**        2. Base/Lib/ErrorHandler.php ...................     **//
//**    4. Base Classes ....................................     **//
//**        1. Base Class ..................................     **//
//**            1. Base Class Properties ...................     **//
//**                1. $instance ...........................     **//
//**                2. $class_cache ........................     **//
//**                3. $class_cache_updated ................     **//
//**                4. $class_paths ........................     **//
//**            2. Base Class Methods ......................     **//
//**                1. __construct() .......................     **//
//**                2. getInstance() .......................     **//
//**                3. addClassPath() ......................     **//
//**                3. loadClass() .........................     **//
//**                4. findClass() .........................     **//
//**                5. _classSearch() ......................     **//
//**                6. _searchDir() ........................     **//
//**                7. __clone() ...........................     **//
//**                8. __destruct() ........................     **//
//**    5. Base Functions ..................................     **//
//**        1. __autoload() ................................     **//
//**    6. Instanciate Base ................................     **//
//**                                                             **//
//*****************************************************************//


//*****************************************************************//
//*********************** Define Contants *************************//
//*****************************************************************//
/**
 *	Define Base Terminal Debug Runlevel Constant
 */
define('BASE_RUNLEVEL_TERM_DEBUG', 30);
/**
 *	Define Base Terminal Notice Runlevel Constant
 */
define('BASE_RUNLEVEL_TERM_NOTICE', 20);
/**
 *	Define Base Terminal Warning Runlevel Constant
 */
define('BASE_RUNLEVEL_TERM_WARN', 10);
/**
 *	Define Base Development Runlevel Constant
 */
define('BASE_RUNLEVEL_DEVEL', 2);
/**
 *	Define Base Production Runlevel Constant
 */
define('BASE_RUNLEVEL_PROD', 1);
/**
 *	Define current version of corelib Base
 */
define('CORELIB_BASE_VERSION', '4.0.0 Beta');
define('CORELIB_BASE_VERSION_MAJOR', '4');
define('CORELIB_BASE_VERSION_MINOR', '0');
define('CORELIB_BASE_VERSION_PATCH', '0');
/**
 * Define CoreLib Copyright owner
 */
define('CORELIB_COPYRIGHT', 'Steffen Soerensen - http://www.corelib.org/');
/**
 * Define CoreLib Copyright year
 */
define('CORELIB_COPYRIGHT_YEAR', '2005-2008');


//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('CORELIB')){
	trigger_error('CORELIB Constant Undefined', E_USER_ERROR);
	/**
	 * 	Corelib Path Constant
	 *
	 * 	This constanst holds the path to the corelib
	 */
	define('CORELIB', '/path/to/corelib/');
}

if(!defined('CURRENT_WORKING_DIR')){
	/**
	 *	Current Working Dir Constant
	 *
	 * 	This constant holds the path to the current working dir
	 */
	define('CURRENT_WORKING_DIR', getcwd().'/');
}

if(!defined('BASE_RUNLEVEL') && false == true){ // this part is for documentation purposes
	/**
	 * Current Runlevel
	 *
	 * This constant holds the current runlevel
	 */
	define('BASE_RUNLEVEL', BASE_RUNLEVEL_DEVEL);
}

if(!defined('BASE_CLASS_CACHE_FILE')){
	/**
	 * Define class cache file
	 *
	 * This constants holds the path, on where to store the class
	 * cache database, this file must be writable by the user running
	 * the script, and it can be overwritten any time before include
	 * {@link Base.php}.
	 */
	define('BASE_CLASS_CACHE_FILE', 'var/db/class.db');
}

if(!defined('BASE_DEFAULT_TIMEZONE')){
	/**
	 * Define default timezone
	 *
	 * Define the default timezone for use in php date functions
	 */
	define('BASE_DEFAULT_TIMEZONE', 'CET');
}

if(!defined('BASE_ADMIN_EMAIL')){
	/**
	 * Define Admin Email
	 *
	 * Define the admin email, for sending runtime informations about erros etc.
	 */
	define('BASE_ADMIN_EMAIL', false);
}
if(!defined('TEMPORARY_DIR')){
	/**
	 * Define Admin Email
	 * 
	 * Define the admin email, for sending runtime informations about erros etc.
	 */
	define('TEMPORARY_DIR', 'var/tmp/');
}

//*****************************************************************//
//******************* Load Base Support Files *********************//
//*****************************************************************//
/**
 * 	Load Interfaces File
 */
require_once(CORELIB.'/Base/Lib/Interfaces.php');
/**
 *	Load Error Handler
 *
 *	To disable the error handler define the constant BASE_DISABLE_ERROR_HANDLER
 * 	and set it to true
 * 
 * @see BASE_DISABLE_ERROR_HANDLER
 */
require_once(CORELIB.'/Base/Lib/Handlers/ErrorHandler.php');


//*****************************************************************//
//************************* Base Classes **************************//
//*****************************************************************//
/**
 *	Base Class
 *
 *	The base class provides all basic functionality, it is also
 *	responsible for controlling some basic PHP features, but the main
 * 	purpose of this file is to control autoloading of class as the are used.
 *
 *	@package corelib
 *	@subpackage Base
 */
class Base implements Singleton {
	//*****************************************************************//
	//********************* Base Class Properties *********************//
	//*****************************************************************//
	/**
	 *	Singleton Object Reference
	 *
	 *	@var Base
	 */
	private static $instance = null;
	/**
	 *	Class Cache
	 *
	 *	Array containing references about in which files
	 * 	the different classes are located.
	 *
	 * @var array
	 */
	private $class_cache = array();
	/**
	 *	Class Cache Change Status
	 *
	 *	Holds informations wether the class cache have been update
	 *	true if the class cache file should be rewritten, false if no
	 *	changes have been made.
	 *
	 *	@var boolean
	 * 	@see Base::__destruct()
	 */
	private $class_cache_updated = false;
	/**
	 *	Class Paths
	 *
	 * 	Holds informations about where classes are stored.
	 *
	 *	@var array
	 * 	@see Base::setClassPaths()
	 */
	private $class_paths = array(CORELIB);


	//*****************************************************************//
	//*********************** Base Class Methods **********************//
	//*****************************************************************//
	/**
	 *	Base Constructor
	 *
	 * 	@uses BASE_CLASS_CACHE_FILE
	 * 	@uses CORELIB_COPYRIGHT_YEAR
	 * 	@uses CORELIB_COPYRIGHT
	 * 	@uses CORELIB_BASE_VERSION
	 * 	@uses Base::$class_cache
	 * 	@uses Base::$class_cache_updated
	 */
	private function __construct(){
		mb_internal_encoding('UTF-8');
		if(php_sapi_name() == 'cli'){
			echo 'Corelib v'.CORELIB_BASE_VERSION." Copyright ".CORELIB_COPYRIGHT_YEAR." ".CORELIB_COPYRIGHT."\n";
		} else {
			header('X-Powered-By: Corelib v'.CORELIB_BASE_VERSION." Copyright ".CORELIB_COPYRIGHT_YEAR." ".CORELIB_COPYRIGHT);
		}
		if(is_callable('date_default_timezone_set')){
			date_default_timezone_set(BASE_DEFAULT_TIMEZONE);
		}
		if(!defined('BASE_RUNLEVEL')){
			/**
			 * Current Runlevel
			 *
			 * This constant holds the current runlevel
			 */
			define('BASE_RUNLEVEL', BASE_RUNLEVEL_DEVEL);
		}
		if(!is_file(BASE_CLASS_CACHE_FILE)){
			$this->class_cache_updated = true;
		}else if(is_writeable(BASE_CLASS_CACHE_FILE) && is_readable(BASE_CLASS_CACHE_FILE)){
			/**
			 * @ignore
			 */
			include_once(BASE_CLASS_CACHE_FILE);
			$this->class_cache = &$classes;
		} else {
			echo '<h1> Class Cache File is unreadable or write-protected</h1>Please check that <b>'.BASE_CLASS_CACHE_FILE.'</b> is readable and writable by the current user.'."\n";
			die;
		}
		require_once(CORELIB.'/Base/Lib/StrictTypes.php');
		$GLOBALS['base'] = $this;
	}

	/**
	 * 	Return instance of Base
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *	@return Base
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Base();
		}
		return self::$instance;
	}

	/**
	 * Add Class Search Path
	 *
	 * @param string $path Relative or complete path, to search for classes
	 * @uses Base::$class_paths
	 */
	public function addClassPath($path){
		try {
			StrictTypes::isString($path);
		} catch (BaseException $e){
			echo $e;
		}
		$this->class_paths[] = $path;
	}

	/**
	 * Force Loading of class
	 *
	 * Force corelib to load a specific class, this is very
	 * usefull for event classes which always needs to be loaded.
	 *
	 * @param string $class Class name
	 * @return boolean always returns true
	 */
	public function loadClass($class){
		try {
			StrictTypes::isString($class);
		} catch (BaseException $e){
			echo $e;
		}
		__autoload($class);
		return true;
	}
	
	/**
	 * Find Class
	 *
	 * Search for a specific class and save it in the class cache.
	 *
	 * @param string $class Name of the class
	 * @return string File containing the class, else return false
	 * @uses Base::_classSearch()
	 * @uses BaseException
	 * @uses Base::$class_cache
	 * @uses Base::$class_cache_updated
	 */
	public function findClass($class){
		try {
			StrictTypes::isString($class);
		} catch (BaseException $e){
			echo $e;
		}
		
		if(!isset($this->class_cache[$class])){
			try {
				if($file = $this->_classSearch($class)){
					$this->class_cache[$class] = $file;
					$this->class_cache_updated = true;
				} else {
		//			throw new BaseException('File containing class '.$class.' could not be found');
				}
			} catch (BaseException $e){
				echo $e->htmlError();
				exit;
			}
		}
		if(isset($this->class_cache[$class])){
			return $this->class_cache[$class];
		} else {
			return false;
		}
	}

	/**
	 * Search for class in directories
	 *
	 * @param string $class Name of the class
	 * @return string File containing the class, else return false
	 * @uses Base::_searchDir()
	 * @uses Base::$class_paths
	 */
	private function _classSearch($class){
		$file = false;
		while(list(,$val) = each($this->class_paths)){
			if($file = $this->_searchDir($val, $class)){
				break;
			}
		}
		reset($this->class_paths);
		return $file;
	}

	/**
	 * Recursive search files for a class
	 *
	 * @param string $dir Directory to look for class in
	 * @param string $class Name of the class to find
	 * @uses Base::_searchDir()
	 * @return string containing the filename, else return false
	 */
	private function _searchDir($dir, $class){
		$fp = dir($dir);
		while($entry = $fp->read()){
			if($entry{0} != '.' && is_dir($dir.'/'.$entry)){
				if($file = $this->_searchDir($dir.'/'.$entry, $class)){
					return $file;
				}
			} else if($entry{0} != '.' && is_readable($dir.'/'.$entry)){
				$content = file_get_contents($dir.'/'.$entry);
				if(preg_match('/(class\s+?'.$class.'\s+?.*?\s*?{)|(interface\s+?'.$class.'\s+?.*?\s*?{)/s', $content, $match)){
					return $dir.'/'.$entry;
				}
			}
		}
		return false;
	}

	/**
	 * Clone function
	 * 
	 * Declared private to prevent cloning
	 * 
	 * @return false
	 */
	private function __clone(){
		return false;
	}
	
	/**
	 * Base Destructor
	 *
	 * The base destructor saves the current class cache, if changed
	 *
	 *	@uses CURRENT_WORKING_DIR
	 * 	@uses BASE_CLASS_CACHE_FILE
	 * 	@uses Base::$class_cache
	 * 	@uses Base::$class_cache_updated
	 */
	public function __destruct(){
		if($this->class_cache_updated){
			$content = '<?php ';
			while(list($key, $val) = each($this->class_cache)){
				$content .= '$classes[\''.$key.'\'] = \''.$val.'\'; ';
			}
			if(!$this->class_cache > 0){
				$content .= '$classes = array();';
			}
			$content .= ' ?>';
			file_put_contents(CURRENT_WORKING_DIR.BASE_CLASS_CACHE_FILE, $content);
			@chmod(CURRENT_WORKING_DIR.BASE_CLASS_CACHE_FILE, 0666);
		}
	}
}


//*****************************************************************//
//************************ Base Functions *************************//
//*****************************************************************//
/**
 * PHP autoload function
 *
 * When a unknown class is used, this function is called.
 * It will then intruct the {@link Base} class to find the
 * file containing the missing class
 *
 * @param string $class Missing class name
 * @uses Base::findClass()
 */
function __autoload($class){
	$base = Base::getInstance();
	if($base->findClass($class)){
		include_once($base->findClass($class));
		return true;
	} else {
		return false;
	}
}
?>