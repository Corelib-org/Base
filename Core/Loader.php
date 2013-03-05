<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base Functions and Classes.
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
 * @category corelib
 * @package Base
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */
namespace Corelib\Base\Core;
use Corelib\Base\Log\Logger,
	Corelib\Base\Core\Exception,
	Corelib\Base\ErrorHandler,
	Corelib\Base\ServiceLocator\Service,
	Corelib\Base\ServiceLocator\Autoloadable,
	Corelib\Base\ServiceLocator\Locator;

//*****************************************************************//
//*********************** Define Contants *************************//
//*****************************************************************//
/**
 *	Define Base Terminal Debug Runlevel Constant.
 */
// define('BASE_RUNLEVEL_TERM_DEBUG', 30);
/**
 *	Define Base Terminal Notice Runlevel Constant.
 */
// define('BASE_RUNLEVEL_TERM_NOTICE', 20);
/**
 *	Define Base Terminal Warning Runlevel Constant.
 */
// define('BASE_RUNLEVEL_TERM_WARN', 10);




//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//




//if(!defined('BASE_RUNLEVEL') && false == true){ // this part is for documentation purposes
	/**
	 * Current Runlevel.
	 *
	 * This constant holds the current runlevel
	 */
//	define('BASE_RUNLEVEL', BASE_RUNLEVEL_DEVEL);
// }



// if(!defined('BASE_CLASS_CACHE_FILE')){
	/**
	 * Define class cache file.
	 *
	 * This constants holds the path, on where to store the class
	 * cache database, this file must be writable by the user running
	 * the script, and it can be overwritten any time before include
	 * Base.php .
	 *
	 * @see Base.php
	 */
//	define('BASE_CLASS_CACHE_FILE', BASE_CACHE_DIRECTORY.'class.db');
// }


//if(!defined('BASE_DEFAULT_TIMEZONE')){
	/**
	 * Define default timezone.
	 *
	 * Define the default timezone for use in php date functions
	 */
//	define('BASE_DEFAULT_TIMEZONE', 'CET');
//}

// if(!defined('TEMPORARY_DIR')){
	/**
	 * Define Admin Email.
	 *
	 * Define the admin email, for sending runtime informations about erros etc.
	 */
//	define('TEMPORARY_DIR', 'var/tmp/');
//}


//*****************************************************************//
//******************* Load Base Support Files *********************//
//*****************************************************************//
/**
 * 	Load Interfaces File.
 */
// require_once(CORELIB.'/Base/lib/Interfaces.php');

//*****************************************************************//
//************************* Base Classes **************************//
//*****************************************************************//
/**
 * Base Class.
 *
 * The base class provides all basic functionality, it is also
 * responsible for controlling some basic PHP features, but the main
 * purpose of this file is to control autoloading of class as the are used.
 *
 * @package Base
 * @author Steffen Sørensen <ss@corelib.org>
 */
class Loader implements Service,Autoloadable {


	//*****************************************************************//
	//********************* Base Class Properties *********************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var Base
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Class Cache.
	 *
	 * Array containing references about in which files
	 * the different classes are located.
	 *
	 * @var array
	 * @internal
	 */
	private $class_cache = array();

	/**
	 * Class Cache Change Status.
	 *
	 * Holds informations wether the class cache have been update
	 * true if the class cache file should be rewritten, false if no
	 * changes have been made.
	 *
	 * @var boolean
	 * @see Base::__destruct()
	 * @internal
	 */
	private $class_cache_updated = false;

	/**
	 * Class Paths.
	 *
	 * Holds informations about where classes are stored.
	 *
	 * @var array
	 * @see Base::setClassPaths()
	 * @internal
	 */
	private $class_paths = array(CORELIB);

	const CLASS_CACHE = 'class.db';


	//*****************************************************************//
	//*********************** Base Class Methods **********************//
	//*****************************************************************//
	/**
	 *	Base Constructor.
	 *
	 * @uses BASE_CLASS_CACHE_FILE
	 * @uses CORELIB_COPYRIGHT_YEAR
	 * @uses CORELIB_COPYRIGHT
	 * @uses CORELIB_BASE_VERSION
	 * @uses BASE_DEFAULT_TIMEZONE
	 * @uses BASE_CLASS_CACHE_FILE
	 * @uses BASE_CLASS_CACHE_FILE
	 * @uses BASE_UMASK
	 * @uses CORELIB
	 * @uses Base::$class_cache
	 * @uses Base::$class_cache_updated
	 * @internal
	 */
	public function __construct(){
		spl_autoload_register(array($this, 'loadClass'));


		// umask(0);

		//if(!defined('BASE_ADMIN_EMAIL')){
			/**
			 * Define Admin Email.
			 *
			 * Define the admin email, for sending runtime informations about erros etc.
			 */
		//	define('BASE_ADMIN_EMAIL', false);
		// }






//		if(!defined('BASE_DISABLE_ERROR_HANDLER') || BASE_DISABLE_ERROR_HANDLER === false){
//			if(!defined('BASE_DISABLE_ERROR_HANDLER')){
				/**
				 * Disable error handler.
				 *
				 * @var boolean true for disabled, false to enable
				 */
//				define('BASE_DISABLE_ERROR_HANDLER', false);
//			}

			/**
			 *	Load Error Handler.
			 *
			 *	To disable the error handler define the constant BASE_DISABLE_ERROR_HANDLER
			 * 	and set it to true
			 *
			 * @see BASE_DISABLE_ERROR_HANDLER
			 * @internal
			 */
		//	require_once(CORELIB.'/Base/lib/Handlers/ErrorHandler.php');



		// }

		/**
		 * Load loopback streams
		 *
		 * @internal
		 */
// 		require_once(CORELIB.'/Base/lib/LoopbackStream.php');

		if(!is_file(BASE_CACHE_DIRECTORY.'/'.self::CLASS_CACHE)){
			$this->class_cache_updated = true;
		} else if(is_readable(BASE_CACHE_DIRECTORY.'/'.self::CLASS_CACHE)){
			/**
			 * @ignore
			 */
			include_once(BASE_CACHE_DIRECTORY.'/'.self::CLASS_CACHE);
			$this->class_cache = &$classes;
		} else {
			echo '<h1> Class Cache File is unreadable </h1>Please check that <b>'.BASE_CACHE_DIRECTORY.'/'.self::CLASS_CACHE.'</b> is readable and writable by the current user.'."\n";
			die;
		}
	}

	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get(__CLASS__);
	}

	/**
	 * Add Class Search Path.
	 *
	 * @param string $path Relative or complete path, to search for classes
	 * @uses Base::$class_paths
	 */
	public function addClassPath($path){
		assert('is_string($path)');
		$this->class_paths[] = $path;
	}

	/**
	 * Force Loading of class.
	 *
	 * Force corelib to load a specific class, this is very
	 * usefull for event classes which always needs to be loaded.
	 *
	 * @param string $class Class name
	 * @return boolean always returns true
	 * @uses __autoload()
	 * @uses StrictTypes::isString()
	 */
	public function loadClass($class){
		assert('is_string($class)');
		if($filename = $this->findClass($class)){
			require_once($filename);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Find Class.
	 *
	 * Search for a specific class and save it in the class cache.
	 *
	 * @param string $class Name of the class
	 * @return string File containing the class, else return false
	 * @uses Base::_classSearch()
	 * @uses Base::$class_cache
	 * @uses Base::$class_cache_updated
	 */
	public function findClass($class){
		assert('is_string($class)');
		assert('$class != "WebPage"');

		if(!isset($this->class_cache[$class])){
			if(preg_match('/^((.*?\\\)+)([\\A-Za-z0-9_]+)$/', $class, $match)){
				list(,$namespace,, $classname) = $match;
				if(substr($namespace, -1, 1) == '\\'){
					$namespace = substr($namespace, 0, -1);
				}
			} else {
				$namespace = null;
				$classname = $class;
			}
			if($file = $this->_classSearch($classname, $namespace)){
				$this->class_cache[$class] = $file;
				$this->class_cache_updated = true;
			} else {
				throw new Exception('File containing class '.$class.' could not be found', E_USER_WARNING);
			}
			Logger::warning('Loaded class: '.$class);
			return $file;
		}
		if(isset($this->class_cache[$class])){
			Logger::debug('Loaded class from cache: '.$class);
			return $this->class_cache[$class];
		} else {
			return false;
		}
	}

	/**
	 * Get registered class paths.
	 *
	 * @uses Base::$class_paths
	 * @return array registered class path's
	 */
	public function getClassPaths(){
		return $this->class_paths;
	}

	/**
	 * Search for class in directories.
	 *
	 * @param string $class Name of the class
	 * @return string File containing the class, else return false
	 * @uses Base::_searchDir()
	 * @uses Base::$class_paths
	 * @internal
	 */
	private function _classSearch($class, $namespace=null){
		set_time_limit(300);
		$file = false;
		while(list(,$val) = each($this->class_paths)){
			if($file = $this->_searchDir($val, $class, $namespace)){
				break;
			}
		}
		reset($this->class_paths);
		return $file;
	}

	/**
	 * Recursive search files for a class.
	 *
	 * @param string $dir Directory to look for class in
	 * @param string $class Name of the class to find
	 * @uses Base::_searchDir()
	 * @return string filename containing the class, else return false
	 * @internal
	 */
	private function _searchDir($dir, $class, $namespace=null){
		$fp = dir($dir);
		while($entry = $fp->read()){
			if($entry{0} != '.' && is_dir($dir.'/'.$entry)){
				if($file = $this->_searchDir($dir.'/'.$entry, $class, $namespace)){
					return $file;
				}
			} else if($entry{0} != '.' && is_readable($dir.'/'.$entry)){
				if(substr($entry, -3) == 'php'){
					$content = file($dir.'/'.$entry);
					$current_namespace = '';
					while(list(,$line) = each($content)){
						if(preg_match('/namespace\s+(.*?)(\{|;)/i', $line, $match)){
							$current_namespace = trim($match[1]);
						}

						if(preg_match('/(class\s+?'.$class.'\s+?.*?\s*?{)|(interface\s+?'.$class.'\s+?.*?\s*?{)/s', $line, $match)){
							if($current_namespace == $namespace){
								return $dir.'/'.$entry;
							}
						}
					}
				}
/*
				$content = file_get_contents($dir.'/'.$entry);
				if(is_null($namespace)){
					if(!preg_match('/^\s*namespace\s+/im', $content, $match)){
						if(preg_match('/(class\s+?'.$class.'\s+?.*?\s*?{)|(interface\s+?'.$class.'\s+?.*?\s*?{)/s', $content, $match)){
							return $dir.'/'.$entry;
						}
					}
				} else {
					if(preg_match('/^\s*namespace\s+('.preg_quote($namespace).')\s*;$/im', $content, $match)){
						if(preg_match('/(class\s+?'.$class.'\s+?.*?\s*?{)|(interface\s+?'.$class.'\s+?.*?\s*?{)/s', $content, $match)){
							return $dir.'/'.$entry;
						}
					}
				}
*/
			}
		}
		return false;
	}

	/**
	 * Clone function.
	 *
	 * Declared private to prevent cloning
	 *
	 * @return false
	 * @internal
	 */
	private function __clone(){
		return false;
	}

	/**
	 * Base Destructor.
	 *
	 * The base destructor saves the current class cache, if changed
	 *
	 * @uses CURRENT_WORKING_DIR
	 * @uses BASE_CLASS_CACHE_FILE
	 * @uses Base::$class_cache
	 * @uses Base::$class_cache_updated
	 * @internal
	 */
	public function __destruct(){
		if($this->class_cache_updated){
			$content = '<?php '."\n";
			while(list($key, $val) = each($this->class_cache)){
				$content .= '$classes[\''.$key.'\'] = \''.$val.'\'; '."\n";
			}
			if(!$this->class_cache > 0){
				$content .= '$classes = array();';
			}

			$cache_file = CURRENT_WORKING_DIR.BASE_CACHE_DIRECTORY.'/'.self::CLASS_CACHE;

			$content .= ' ?>';
			if(!is_dir(dirname($cache_file))){
				if(!is_writable(dirname(dirname($cache_file)))){
					echo '<div style="margin: 20px;"><h1>Unable to create directory "'.dirname($cache_file).'"</h1>';
					echo '<p>Please make the directory <b>'.dirname(dirname($cache_file)).'</b> writable to the webuser.</p><br/>';
					echo '<pre>$ chmod -R uga=+rwX '.dirname(dirname($cache_file)).'</pre></div>';
				} else {
					mkdir(dirname($cache_file), 0777, true);
				}
			}
			@file_put_contents($cache_file, $content);
			@chmod($cache_file, 0666);
		}
	}
}
?>