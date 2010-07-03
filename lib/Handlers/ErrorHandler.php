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
 * @category corelib
 * @package Base
 * @subpackage ErrorHandler
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 2.0.0 ($Id$)
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
// Set error reporting level
error_reporting(E_ALL | E_STRICT);

if(!defined('BASE_ERROR_LOGFILE')){
	/**
	 * Define error log file.
	 *
	 * @var string filename
	 */
	define('BASE_ERROR_LOGFILE','var/log/error.log');
}

if(false){ // For documentational purpose only
	/**
	 * Set fatal error redirect target.
	 *
	 * If this constant is defined the error handler will
	 * automatically redirect users to page that can show
	 * a error has occured.
	 *
	 * @var string url to error handling page.
	 */
	define('BASE_ERROR_FATAL_REDIRECT','http://'.$_SERVER['SERVER_NAME'].'/corelib/report/');
}


//*****************************************************************//
//*********************** ErrorHandler class **********************//
//*****************************************************************//
/**
 * Error Handler class.
 *
 * @category corelib
 * @package Base
 * @subpackage ErrorHandler
 * @since 5.0
 */
class ErrorHandler implements Singleton {


	//*****************************************************************//
	//*************** ErrorHandler class properties *******************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var ErrorHandler
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Error list.
	 *
	 * @var array
	 * @internal
	 */
	private $errors = array();


	//*****************************************************************//
	//**************** ErrorHandler class constants *******************//
	//*****************************************************************//
	/**
	 * Number of lines to display around the error.
	 *
	 * @var integer
	 * @internal
	 */
	const SOURCE_LINES = 12;


	//*****************************************************************//
	//**************** ErrorHandler class methods *******************//
	//*****************************************************************//
	/**
	 * Error handler constructor.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){ }

	/**
	 * 	Return instance of ErrorHandler.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses ErrorHandler::$instance
	 *	@return ErrorHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new ErrorHandler();
		}
		return self::$instance;
	}

	/**
	 * Have any errors been cought.
	 *
	 * @return boolean true of errors have been cought else return false
	 */
	public function hasErrors(){
		if(count($this->errors) > 0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Trigger error.
	 *
	 * This method is used by php's internal errorhandler.
	 *
	 * @param integer $code error code.
	 * @param string $description error description
	 * @param string $file filename
	 * @param integer $line line number
	 * @param object $symbol object reference
	 * @return void
	 * @internal
	 */
	public function trigger($code, $description, $file=null, $line=null, $symbol=null){
		if(error_reporting() != 0){
			/**
			 * XXX This if loop is a hot fix for disabling E_STRICT errors for all php 5.2.x version.
			 * XXX This works as a workaround for php bug #49177, see: http://bugs.php.net/bug.php?id=49177 for more information
			 */
			if($code != E_STRICT || !version_compare(PHP_VERSION, '5.3') == -1){
 				header('HTTP/1.1 500 Internal Server Error');
				$this->errors[] = array('code' => $code,
				                        'description' => $description,
				                        'file' => $file,
				                        'line' => $line,
				                        'symbol' => $symbol,
				                        'backtrace' => debug_backtrace());
			}
		}
	}

	/**
	 * Trigger assert
	 *
	 * This method is used by PHP's internal assert handler
	 *
	 * @param string $file filename
	 * @param integer $line line number
	 * @param string $message infomtion about failed assert
	 * @return void
	 * @internal
	 */
	public function assert($file, $line, $message=null){
		$this->trigger(E_USER_ERROR, 'Assertion Failed: '.$message, $file, $line);
	}

	/**
	 * Fatal error handler
	 *
	 * If the error handler detects a fatal error
	 * which normally would'nt be cought this method
	 * will scan the content of the output buffer instead.
	 *
	 * @param string output buffer
	 * @return string new output buffer
	 * @see ErrorHandler::draw();
	 * @internal
	 */
	public function fatal($buffer){
		if(!strstr($buffer, '<b>Fatal error</b>:')){
			return false;
		} else {
			preg_match_all('/\<br \/\>\s\<b\>(.*?)\<\/b\>:\s*(.*?)\sin\s.*?\<b\>(.*?)\<\/b\>\s*on\s*line\s*\<b\>(.*?)<\/b\>\<br \/\>/s', $buffer, $result);
			while(list($key, $val) = each($result[0])){
 				$this->trigger(E_USER_ERROR, $result[2][$key], $result[3][$key], $result[4][$key]);
			}
			return $this->draw();
		}
	}

	/**
	 * Draw error and send correct headers.
	 *
	 * @return string error message
	 */
	public function draw(){
		header('Content-Type: text/html; charset=utf-8');
		$buffer = $this->__toString();
		$checksum = md5($buffer);
		if(BASE_RUNLEVEL < BASE_RUNLEVEL_DEVEL && php_sapi_name() != 'cli'){
			if(BASE_ADMIN_EMAIL !== false){
				mail(BASE_ADMIN_EMAIL, '['.$_SERVER['SERVER_NAME'].' - Corelib error handler - '.$checksum.'] '.$result[2][$key], $buffer, 'Content-Type: text/html');
			}
			if(defined('BASE_ERROR_FATAL_REDIRECT')){
				$buffer = '<html><head><meta http-equiv="refresh" content="0;URL='.BASE_ERROR_FATAL_REDIRECT.'?checksum='.$checksum.'"></head></hmtl>';
			} else {
				$buffer ='<html><head><title>500 Internal Server Error</title></head><body><h1>Internal Server Error</h1>The server encountered an internal error or misconfiguration and was unable to complete your request.<p>ID: '.$checksum.'</p><hr><i><a href="http://www.corelib.org/">Corelib v'.CORELIB_BASE_VERSION.'</a></i></body></html>';
			}
		}
		return $buffer;
	}


	/**
	 * Convert error handler to string.
	 *
	 * @return string error handler content.
	 */
	public function __toString(){
		$template = file_get_contents(CORELIB.'/Base/Share/Templates/ErrorTemplate.tpl');
		$content = preg_replace('/^.*?\!ERROR_TEMPLATE {(.*?)}.*/ms', '\\1', $template);
		$buffer = '';
		foreach ($this->errors as $error){
			$buffer .= $this->_getError($error, $content);
		}
		$template = preg_replace('/^(.*?)\!ERROR_TEMPLATE {.*?}(.*?)$/ms', '\\1 '.$buffer.' \\3', $template);
		return $template;
	}


	/**
	 * Merge error template with error content.
	 *
	 * @param array $error error information
	 * @param string $content template
	 * @return string
	 * @internal
	 */
	private function _getError(array &$error, $content){
		$return = str_replace('!ERROR_NAME!', $error['code'].': '.$this->_getErrorCodeDescription($error['code']), $content);
		$return = str_replace('!ERROR_DESC!', $error['description'], $return);
		$return = str_replace('!ERROR_FILE!', $error['file'], $return);
		$return = str_replace('!ERROR_LINE!', $error['line'], $return);
		$return = str_replace('!ERROR_FILE_CONTENT!', $this->_getSource($error), $return);
		$return = str_replace('!ERROR_FILE_CONTENT_ID!', RFC4122::generate(), $return);
		$return = str_replace('!CORELIB_VERSION!', CORELIB_BASE_VERSION, $return);
		$return = str_replace('!CORELIB_COPYRIGHT!', CORELIB_COPYRIGHT, $return);
		$return = str_replace('!CORELIB_COPYRIGHT_YEAR!', CORELIB_COPYRIGHT_YEAR, $return);
		$return = str_replace('!STACK_TRACE!', $this->getTraceAsHTML($error['backtrace']), $return);
		$return = str_replace('!STACK_TRACE_ID!', RFC4122::generate(), $return);
		$return = str_replace('!REQUEST_CONTENT!', $this->_getRequestContentAsHTML(), $return);
		$return = str_replace('!REQUEST_CONTENT_ID!', RFC4122::generate(), $return);
		return $return;
	}

	/**
	 * Translate error code to string.
	 *
	 * @param integer $code
	 * @return string error description
	 * @internal
	 */
	private function _getErrorCodeDescription($code){
		switch ($code) {
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

	/**
	 * Get trace as HTML.
	 *
	 * @param array $trace
	 * @return string
	 */
	public function getTraceAsHTML(array &$trace){
		$return = '';
		foreach ($trace as $key => $level){
			if(isset($level['class']) && !empty($level['class'])){
				$function = $level['class'].'->'.$level['function'];
			} else {
				$function = $level['function'];
			}
			$args = array();
			if(isset($level['args'])){
				foreach ($level['args'] as $arg){
					if(is_array($arg)){
						$args[] = 'array('.sizeof($arg).')';
					} else if(is_object($arg)){
						$args[] = get_class($arg);
					} else if(is_string($arg)) {
						if(strlen($arg) > 30){
							$args[] = '\'<span title="'.htmlspecialchars($arg).'">'.htmlspecialchars(substr($arg, 0, 30)).'...</span>\'';
						} else {
							$args[] = '\''.htmlspecialchars($arg).'\'';
						}
					} else {
						$args[] = $arg;
					}
				}
				$return .= '#'.($key + 1).' '.$level['file'].'('.$level['line'].')'.' '.htmlspecialchars($function).'('.implode(', ', $args).')'."<br/>";
			}
		}
		return $return;
	}

	/**
	 * Get highlighted source cut.
	 *
	 * @param array $error
	 * @return string
	 * @internal
	 */
	private function _getSource(array &$error){
		$source = file_get_contents($error['file']);
		$source = explode('<br />', $source);
		$source = file($error['file']);
		if($error['line'] < self::SOURCE_LINES){
			$offset = 1;
		} else {
			$offset = $error['line'] - self::SOURCE_LINES;
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
				if($error['line'] == ($i + 1)){
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
					$source[$i] = preg_replace('/\b(public|private|implements|const|^\s*(abstract)?\s*class|extends|protected|static|function|require_once|require|include_once|include|if|else|while|new|null|true|false|isset|return|self|echo|exit|try|throw|catch)\b/', '<span style="color: #0000ff">\\0</span>', $source[$i]);
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

	/**
	 * Get request content as HTML
	 *
	 * @return string
	 * @internal
	 */
	private function _getRequestContentAsHTML(){
		$return = '<b>$_SERVER</b><table>';
		foreach($_SERVER as $key => $data){
			$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
		}
		$return .= '</table>';
		if(sizeof($_GET) > 0){
		$return .= '<br/><b>$_GET</b><table>';
			foreach($_GET as $key => $data){
				$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
			}
			$return .= '</table>';
		}
		if(sizeof($_POST) > 0){
			$return .= '<br/><b>$_POST</b><table>';
			foreach($_POST as $key => $data){
				$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
			}
			$return .= '</table>';
		}
		return $return;
	}
}

//*****************************************************************//
//********************** BaseException class **********************//
//*****************************************************************//
/**
 * Base exception class.
 *
 * @category corelib
 * @package Base
 * @subpackage ErrorHandler
 */
class BaseException extends Exception {


	//*****************************************************************//
	//***************** BaseException class methods *******************//
	//*****************************************************************//
	/**
	 * Overwrite parent constructor.
	 *
	 * override original constructor in order to add support for
	 * both PHP < 5.3 where exception linking where unsupported and
	 * >= PHP 5.3 where exception linking is supported
	 *
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 * @return void
	 */
	public function __construct($message = null, $code = 0, Exception $previous = null){
		if(version_compare(PHP_VERSION, '5.3') == -1){
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}

	public function __toString(){
		trigger_error('Uncought exception: '.get_class($this).' - '.$this->getMessage(), E_USER_ERROR);
		return parent::__toString();
	}
}


//*****************************************************************//
//****************** Last minute configuration ********************//
//*****************************************************************//
if(!defined('BASE_DISABLE_ERROR_HANDLER') || BASE_DISABLE_ERROR_HANDLER === false){
	if(BASE_RUNLEVEL > BASE_RUNLEVEL_PROD){
		assert_options(ASSERT_ACTIVE, true);
		assert_options(ASSERT_BAIL, false);
		assert_options(ASSERT_WARNING, false);
		assert_options(ASSERT_CALLBACK, array(ErrorHandler::getInstance(), 'assert'));
	} else {
		assert_options(ASSERT_ACTIVE, false);
	}
	ini_set('html_errors',true);
	set_error_handler(array(ErrorHandler::getInstance(), 'trigger'));
	ob_start(array(ErrorHandler::getInstance(), 'fatal'));
}
?>