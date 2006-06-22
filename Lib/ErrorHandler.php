<?php
if(!defined('BASE_DISABLE_ERROR_HANDLER') || BASE_DISABLE_ERROR_HANDLER === false){
	if(!defined('BASE_DISABLE_ERROR_HANDLER')){
		define('BASE_DISABLE_ERROR_HANDLER', false);
	}
	set_error_handler('BaseError');
}

if(!defined('BASE_ERROR_LOGFILE')){
	define('BASE_ERROR_LOGFILE','var/log/errors');
}

/**
 * 	PHP error handler
 * 
 * 	This function is used for capturing PHP errors.
 * 	The function captures PHP errors triggered either
 * 	by programming errors, or errors triggered by the 
 * 	PHP trigger_error() function.
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


class BaseException extends Exception {
	private static $buffer = false;
	private static $template = null;
	private static $template_desc = null;

	function __construct($msg, $code=0) {
		parent::__construct($msg, $code);
		if(!self::$buffer){
			ob_start();
			self::$buffer = true;
			self::$template = file_get_contents(CORELIB.'/Base/Share/Templates/ErrorTemplate.tpl');
			self::$template_desc = preg_replace('/^.*?\!ERROR_TEMPLATE {(.*?)}.*/ms', '\\1', self::$template);
		}
	}

	function __toString(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			var_dump(BASE_RUNLEVEL);
			if(php_sapi_name() == 'cli'){
				return $this->WriteToLog(true);
			} else {
				return $this->htmlError();
			}
		} else {
			return $this->WriteToLog();
		}
	}

	function htmlError(){
		$return = str_replace('!ERROR_NAME!', ($this->myGetCode()), self::$template_desc);
		$return = str_replace('!ERROR_DESC!', $this->getMessage(), $return);
		$return = str_replace('!ERROR_FILE!', $this->getFile(), $return);
		$return = str_replace('!ERROR_LINE!', $this->getLine(), $return);
		$return = str_replace('!CORELIB_VERSION!', CORELIB_BASE_VERSION, $return);
		$return = str_replace('!CORELIB_COPYRIGHT!', CORELIB_COPYRIGHT, $return);
		$return = str_replace('!CORELIB_COPYRIGHT_YEAR!', CORELIB_COPYRIGHT_YEAR, $return);
		$return = str_replace('!STACK_TRACE!', nl2br(htmlentities($this->getTraceAsString())), $return);
		return $return;
	}
	
	function WriteToLog($return = false){
		$content = '--====MD5 '.md5($this->getFile().$this->getLine()).' Time: '.date('r')."\n";
		$content .= 'Error Code: '.$this->myGetCode()."\n";
		$content .= 'Error File: '.$this->getFile()."\n";
		$content .= 'Error Line: '.$this->getLine()."\n";
		if(isset($_SERVER['REQUEST_URI'])){
			$content .= 'Request URI: '.$_SERVER['REQUEST_URI']."\n";	
		}
		if(isset($_SERVER['HTTP_REFERER'])){
			$content .= 'HTTP Referer: '.$_SERVER['HTTP_REFERER']."\n";	
		}
		if(isset($_SERVER['REMOTE_ADDR'])){
			$content .= 'Remote Address: '.$_SERVER['REMOTE_ADDR']."\n";	
		}
		$content .= "\n";
		$content .= strip_tags(preg_replace('/\<br\/\\>\s*/', "\n", $this->getMessage()))."\n\n";
		$content .= $this->getTraceAsString()."\n";
		$content .= 'EOF====--'."\n\n";
		if(!$return){
			$fp = fopen(BASE_ERROR_LOGFILE, 'a+');
			fwrite($fp, $content, strlen($content));
			fclose($fp);
			return '';
		} else {
			return $content;
		}
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
		if(BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
			return self::$buffer;
		} else {
			return false;
		}
	}

	static function getErrorPage(){
		$content = ob_get_contents();
		ob_end_clean();
		if(php_sapi_name() != 'cli'){
			return preg_replace('/^(.*?)\!ERROR_TEMPLATE {.*?}(.*?)$/ms', '\\1 '.$content.' \\3', self::$template);
		} else {
			return  $content;
		}
	}
}
?>