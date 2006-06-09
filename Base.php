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
		return $this->htmlError();
	}

	function htmlError(){
		$return = str_replace('!ERROR_NAME!', ($this->myGetCode()), self::$template_desc);
		$return = str_replace('!ERROR_DESC!', $this->getMessage(), $return);
		$return = str_replace('!ERROR_FILE!', $this->getFile(), $return);
		$return = str_replace('!ERROR_LINE!', $this->getLine(), $return);
		$return = str_replace('!STACK_TRACE!', nl2br(htmlentities($this->getTraceAsString())), $return);
		return $return;
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
		return self::$buffer;
	}

	static function getErrorPage(){
		$content = ob_get_contents();
		ob_end_clean();
		return preg_replace('/^(.*?)\!ERROR_TEMPLATE {.*?}(.*?)$/ms', '\\1 '.$content.' \\3', self::$template);
	}
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
?>