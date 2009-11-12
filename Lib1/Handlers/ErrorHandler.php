<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Error Handling Functions and Classes
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
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */
error_reporting(E_ALL | E_STRICT);

if(!defined('BASE_DISABLE_ERROR_HANDLER') || BASE_DISABLE_ERROR_HANDLER === false){
	if(!defined('BASE_DISABLE_ERROR_HANDLER')){
		define('BASE_DISABLE_ERROR_HANDLER', false);
	}

	if(BASE_RUNLEVEL > BASE_RUNLEVEL_PROD){
		assert_options(ASSERT_ACTIVE, true);
		assert_options(ASSERT_BAIL, true);
		assert_options(ASSERT_WARNING, true);
	} else {
		assert_options(ASSERT_ACTIVE, false);
	}

	ini_set('html_errors',true);
	set_error_handler('BaseError');
	ob_start('BaseFatalError');
}

if(!defined('BASE_ERROR_LOGFILE')){
	define('BASE_ERROR_LOGFILE','var/log/error.log');
}
/*
if(!defined('BASE_ERROR_FATAL_REDIRECT') && isset($_SERVER['SERVER_NAME'])){
	define('BASE_ERROR_FATAL_REDIRECT','http://'.$_SERVER['SERVER_NAME'].'/corelib/report/');
}
*/

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
			/**
			 * XXX This if loop is a hot fix for disabling E_STRICT errors for all php 5.2.x version.
			 * XXX This works as a workaround for php bug #49177, see: http://bugs.php.net/bug.php?id=49177 for more information
			 */
			if($errno != E_STRICT || !(version_compare(PHP_VERSION, '5.2') == 1 && version_compare(PHP_VERSION, '5.3') == -1)){
				throw new BaseException(htmlentities($errstr).' <br/><i> '.$errfile.' at line '.$errline.'</i>', $errno, $errstr, $errfile, $errline, $errorcontext);
			}
		}
	} catch (BaseException $e){
		echo $e;
		return $e;
	}
	return true;
}

function BaseFatalError($buffer){
	if(!strstr($buffer, '<b>Fatal error</b>:')){
		return false;
	} else {
		preg_match_all('/\<br \/\>\s\<b\>(.*?)\<\/b\>:\s*(.*?)\sin\s.*?\<b\>(.*?)\<\/b\>\s*on\s*line\s*\<b\>(.*?)<\/b\>\<br \/\>/s', $buffer, $result);
		while(list($key, $val) = each($result[0])){
			$buffer = str_replace($result[0][$key], '', $buffer);
			$e = BaseError(E_USER_ERROR, $result[2][$key], $result[3][$key], $result[4][$key]);
			$buffer .= $e->__toString();

			$checksum = $e->getChecksum();
			if(BASE_RUNLEVEL < BASE_RUNLEVEL_DEVEL && php_sapi_name() != 'cli'){
				if(BASE_ADMIN_EMAIL !== false){
					mail(BASE_ADMIN_EMAIL, '[Corelib Error Handler] '.$result[2][$key], $e->writeToLog(true));
				}
				if(defined('BASE_ERROR_FATAL_REDIRECT')){
					$buffer = '<html><head><meta http-equiv="refresh" content="0;URL='.BASE_ERROR_FATAL_REDIRECT.'?checksum='.$checksum.'"></head></hmtl>';
				} else {
					$buffer ='<html><head><title>500 Internal Server Error</title></head><body><h1>Internal Server Error</h1>The server encountered an internal error or misconfiguration and was unable to complete your request.<p>ID: '.$checksum.'</p><hr><i><a href="http://www.corelib.org/">Corelib</a></i></body></html>';
				}
			}
		}
		return $buffer;
	}
}

class BaseException extends Exception {
	private static $buffer = false;
	private static $template = null;
	private static $template_desc = null;

	private $errstr = null;
	private $errfile = null;
	private $errline = null;
	private $errorcontext = null;
	private $checksum = null;

	const SOURCE_LINES = 12;

	function __construct($msg, $code=0, $errstr=null, $errfile=null, $errline=null, $errorcontext=null) {
		parent::__construct($msg, $code);
		$this->errstr = $errstr;
		$this->errfile = $errfile;
		$this->errline = $errline;
		$this->errorcontext = $errorcontext;
		$this->checksum = md5($this->getFile().$this->getLine());
		if(!self::$buffer){
			self::$buffer = true;
			self::$template = file_get_contents(CORELIB.'/Base/Share/Templates/ErrorTemplate.tpl');
			self::$template_desc = preg_replace('/^.*?\!ERROR_TEMPLATE {(.*?)}.*/ms', '\\1', self::$template);
		}
	}

	function __toString(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			if(php_sapi_name() == 'cli'){
				return $this->WriteToLog(true);
			} else {
				return $this->htmlError();
			}
		} else {
			return $this->WriteToLog();
		}
	}

	public function myGetFile(){
		if(!is_null($this->errfile)){
			return $this->errfile;
		} else {
			return $this->getFile();
		}
	}
	public function myGetLine(){
		if(!is_null($this->errline)){
			return $this->errline;
		} else {
			return $this->getLine();
		}
	}
	public function myGetMessage(){
		if(!is_null($this->errstr)){
			return $this->errstr;
		} else {
			return $this->getMessage();
		}
	}
	public function getChecksum(){
		return $this->checksum;
	}


	function htmlError(){
		$return = str_replace('!ERROR_NAME!', ($this->getCode().': '.$this->myGetCode()), self::$template_desc);
		$return = str_replace('!ERROR_DESC!', $this->myGetMessage(), $return);
		$return = str_replace('!ERROR_FILE!', $this->myGetFile(), $return);
		$return = str_replace('!ERROR_LINE!', $this->myGetLine(), $return);
		$return = str_replace('!ERROR_FILE_CONTENT!', $this->getSource(), $return);
		$return = str_replace('!CORELIB_VERSION!', CORELIB_BASE_VERSION, $return);
		$return = str_replace('!CORELIB_COPYRIGHT!', CORELIB_COPYRIGHT, $return);
		$return = str_replace('!CORELIB_COPYRIGHT_YEAR!', CORELIB_COPYRIGHT_YEAR, $return);
		$return = str_replace('!STACK_TRACE!', nl2br(htmlentities($this->getTraceAsString())), $return);
		$this->getSource();
		return $return;
	}

	function getSource(){
		$source = file_get_contents($this->myGetFile());
		$source = explode('<br />', $source);
		$source = file($this->myGetFile());
		if($this->myGetLine() < self::SOURCE_LINES){
			$offset = 1;
		} else {
			$offset = $this->myGetLine() - self::SOURCE_LINES;
		}
		if(($offset + (self::SOURCE_LINES * 2)) > sizeof($source)){
			$offset = sizeof($source) - (self::SOURCE_LINES * 2);
		}
		$offset--;
		$content = '';
		$instring = false;
		for ($i = $offset; $i <= $offset + (self::SOURCE_LINES * 2); $i++){
			if(isset($source[$i])){
				$source[$i] = htmlspecialchars($source[$i]);
				if($this->mygetLine() == ($i + 1)){
					$style="background-color: #FFCCCC;";
				} else {
					$style="";
				}

				if(!$instring){
					if(preg_match('/[\'"].*?\n/s', $source[$i]) && !preg_match('/[\'"].*?[\'"\n]/s', $source[$i])){
						$instring = true;
					}
					$source[$i] = preg_replace('/[\'"].*?[\'"\n]/s', '<span style="color: #008200">\\0</span>', $source[$i]);
					$source[$i] = preg_replace('/\$[[:alpha:]_]+/', '<span style="color: #ad2e00">\\0</span>', $source[$i]);
					$source[$i] = preg_replace('/\b(abstract|public|private|implements|const|class|extends|protected|static|function|require_once|require|include_once|include|if|else|while|new|null|true|false|isset|return|self|echo|exit|try|throw|catch)\b/', '<span style="color: #0000ff">\\0</span>', $source[$i]);
				} else {
					if(preg_match('/.*?[\'"]/', $source[$i])){
						$instring = false;
					}
					$source[$i] = preg_replace('/.*?[\'"\n]/', '<span style="color: #008200">\\0</span>', $source[$i]);
				}


				$source[$i] = str_replace("\t", '&#160;&#160;&#160;&#160;', $source[$i]);

				$content .= '<div style="line-height: 16px; font-family: monospace; '.$style.'">'.($i + 1).': '.($source[$i]).'</div>';
			}
		}
		return $content;
	}

	function WriteToLog($return = false){
		$content = '--====MD5 '.$this->checksum.' Time: '.date('r')."\n";
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
			case E_USER_NOTICE:
				return 'Notice';
				break;
			case E_NOTICE:
				return 'Notice';
				break;
			case E_STRICT:
				return 'Strict';
			default:
				return "Unknown Error";
				break;
		}
	}

	static function IsErrorThrown(){
		$base = Base::getInstance();
		if(self::$buffer !== false){
			header('HTTP/1.1 503 Internal Server Error');
			header('Status: 503');
		}
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