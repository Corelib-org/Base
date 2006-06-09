<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	Corelib Base Functions and Classes
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the 
 *	Morkland Distribution license that is available through the 
 *	world-wide-web at the following URI: http://www.morkland.com/rd/license/dist_1.0.txt.
 *	If you did not receive a copy of the PHP License and are
 *	unable to obtain it through the web, please send a note to 
 *	license@morkland.com so we can mail you a copy immediately.
 *
 *	@author Steffen S&Oslash;rensen <steffen@morkland.com>
 *	@copyright Copyright &copy; 2005, Morkland
 * 	@license http://www.morkland.com/rd/licence/dist_1.0.txt
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.morkland.com/
 *	@version 1.0.0 ($Id$)
 */

set_error_handler('BaseError');

require_once(CORELIB.'/Base/Interfaces.php');

class BaseException extends Exception {
	private static $buffer = false;
	private static $template = null;
	private static $template_desc = null;

	function __construct($msg, $code=0) {
		parent::__construct($msg, $code);
		if(!self::$buffer){
			ob_start();
			self::$buffer = true;
			self::$template = file_get_contents(CORELIB.'/Base/SupportFiles/ErrorTemplate.tpl');
			self::$template_desc = preg_replace('/^.*?\!ERROR_TEMPLATE {(.*?)}.*/ms', '\\1', self::$template);
		}
	}

	function __toString(){
		$base = Base::getInstance();
		if($base->getRunLevel() == BASE_RUNLEVEL_DEVEL){
			return $this->htmlError();
		} else {
			return $this->WriteToLog();
		}
	}

	function htmlError(){
		$return = str_replace('!ERROR_NAME!', ($this->myGetCode()), self::$template_desc);
		$return = str_replace('!ERROR_DESC!', $this->getMessage(), $return);
		$return = str_replace('!ERROR_FILE!', $this->getFile(), $return);
		$return = str_replace('!ERROR_LINE!', $this->getLine(), $return);
		$return = str_replace('!STACK_TRACE!', nl2br(htmlentities($this->getTraceAsString())), $return);
		return $return;
	}
	
	function WriteToLog(){
		$content = '--====MD5 '.md5($this->getFile().$this->getLine()).' Time: '.date('r')."\n";
		$content .= 'Error Code: '.$this->myGetCode()."\n";
		$content .= 'Error File: '.$this->getFile()."\n";
		$content .= 'Error Line: '.$this->getLine()."\n\n";
		$content .= $this->getMessage()."\n\n";
		$content .= $this->getTraceAsString()."\n";
		$content .= 'EOF====--'."\n\n";
		$base = Base::getInstance();
		$fp = fopen($base->getErrorLogFile(), 'a+');
		fwrite($fp, $content, strlen($content));
		fclose($fp);
		return '';
	}

	function myGetCode(){
		switch ($this->getCode()) {
			case E_USER_ERROR:
				return 'Error';
				break;
			case E_USER_WARNING:
				return 'Warning';
				break;
			case E_USER_NOTICE || E_NOTICE:
				return 'Notice';
				break;
			default:
				return "Unknown Error";
				break;
		}
	}
	
	static function IsErrorThrown(){
		$base = Base::getInstance();
		if($base->getRunLevel() == BASE_RUNLEVEL_DEVEL){
			return self::$buffer;
		} else {
			return false;
		}
	}

	static function getErrorPage(){
		$content = ob_get_contents();
		ob_end_clean();
		return preg_replace('/^(.*?)\!ERROR_TEMPLATE {.*?}(.*?)$/ms', '\\1 '.$content.' \\3', self::$template);
	}
}

define('BASE_RUNLEVEL_DEVEL', 1);
define('BASE_RUNLEVEL_PROD', 2);

class Base implements Singleton {
	private static $instance = null;	
	
	private $run_level = BASE_RUNLEVEL_DEVEL;
	private $class_cache = array();
	private $class_cache_file = 'cache/class_cache.dat';
	private $class_cache_updated = false;
	private $class_paths = array(CORELIB);
	private $working_dir = null;
	private $error_log_file = 'cache/error.log';
	
	private function __construct(){
		header('X-Powered-By:');
		$this->working_dir = getcwd().'/';
	}
	
	/**
	 *	@return Base
	 */
	public static function &getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Base();
		}
		return self::$instance;			
	}
	
	public function init(){
		if(!is_file($this->class_cache_file)){
			$this->class_cache_updated = true;
		}else if(is_writeable($this->class_cache_file) && is_readable($this->class_cache_file)){
			include_once($this->class_cache_file);
			$this->class_cache = &$classes;
		} else {
			echo '<h1> Class Cache File is unreadable or write-protected</h1>Please check that <b>'.$this->class_cache_file.'</b> is readable and writable by the current user.';
		}
	}
	
	public function setClassCacheFile($filename){
		$this->class_cache_file = $filename;
	}

	public function setErrorLogFile($filename){
		$this->error_log_file = $filename;
	}
	
	public function setRunLevel($level){
		$this->run_level = $level;
	}
	
	public function getRunLevel(){
		return $this->run_level;
	}
	public function getErrorLogFile(){
		return $this->error_log_file;
	}
	
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
			file_put_contents($this->working_dir.$this->class_cache_file, $content);
		}
	}
	
	public function setClassPaths(){
		$this->class_paths = func_get_args();
	}
	
	public function findClass($class){
		if(!isset($this->class_cache[$class])){
			try {
				if($file = $this->classSearch($class)){
					$this->class_cache[$class] = $file;
					$this->class_cache_updated = true;
				} else {
					throw new BaseException('File containing class '.$class.' could not be found');
				}
			} catch (Exception $e){
				echo $e;
				exit;
			}
		}
		return $this->class_cache[$class];
	}
	
	public function classSearch($class){
		$file = false;
		while(list(,$val) = each($this->class_paths)){
			if($file = $this->_searchDir($val, $class)){
				break;
			}
		}
		reset($this->class_paths);
		return $file;
	}
	
	private function _searchDir($dir, $class){
		$fp = dir($dir);
		while($entry = $fp->read()){
			if($entry{0} != '.' && is_dir($dir.'/'.$entry)){
				if($file = $this->_searchDir($dir.'/'.$entry, $class)){
					return $file;
				}
			} else if($entry{0} != '.' && is_readable($dir.'/'.$entry)){
				$content = file_get_contents($dir.'/'.$entry);
				if(preg_match('/(class '.$class.'.*?\S*?{)|(interface '.$class.'.*?\S*?{)/ms', $content)){
					return $dir.'/'.$entry;
				}
			}
		}
		return false;
	}
}

function __autoload($class){
	$base = Base::getInstance();
	include_once($base->findClass($class));
}

/**
 * 	PHP error handler
 * 
 * 	This function is used for capturing PHP errors.
 * 	The function captures PHP errors triggered either
 * 	by programming errors, or errors triggered by the 
 * 	PHP trigger_error() function.
 * 
 * 
 * 	<b>Function Flow</b>
 * 
 *	try 
 *		if error_reporting does not match 0
 *			then trow BaseException
 *	catch the BaseException and
 *		print content of BaseException
 * 
 *	@param integer $errno
 *	@param string $errstr
 *	@param string $errfile
 *	@param integer $errline
 *	@param string $errorcontext
 *	@uses BaseException
 */
function BaseError($errno, $errstr, $errfile, $errline, $errorcontext){
	try {
		if(error_reporting() != 0){
			throw new BaseException(htmlentities($errstr).' <br/><i> '.$errfile.' at line '.$errline.'</i>', $errno);
		}
	} catch (BaseException $e){
		echo $e;
	}
}

/**
 *	Check if string contains http:// or https://
 *
 *	@param string $str subject, string to test whether or not it contains http:// or https://
 *	@return boolean returns true if $str contains http:// or https://, else return false
 */
function contains_http($str){
	return (preg_match('(^(http:\/\/))', $str) || preg_match('(^(https:\/\/))', $str));
}
/*
 *	Deprecated / Not used
function is_utf8($string){
	echo 'Encoding:'.mb_detect_encoding($string, "auto").'<br/>'."\n";
	return stristr(mb_detect_encoding($string, "auto"), 'UTF-8');
}
*/
?>