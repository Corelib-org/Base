<?php
namespace Corelib\Base\Core;

use Corelib\Base\ServiceLocator\Service;

/**
 * Error Handler class.
 *
 * @category corelib
 * @package Base
 * @subpackage ErrorHandler
 * @since 5.0
 */
class ErrorHandler implements Service {


	//*****************************************************************//
	//*************** ErrorHandler class properties *******************//
	//*****************************************************************//
	/**
	 * Error list.
	 *
	 * @var array
	 * @internal
	 */
	private $errors = array();

	private $template = null;


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
	public function __construct(){
		$this->template = realpath(CORELIB.'/Base/share/Templates/ErrorTemplate.tpl');
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
			if(version_compare(PHP_VERSION, '5.3') >= 0 || $code != E_STRICT){
				if(php_sapi_name() != 'cli'){
					header('HTTP/1.1 500 Internal Server Error');
				}
				$this->errors[] = array('code' => $code,
				                        'description' => $description,
				                        'file' => $file,
				                        'line' => $line,
				                        'symbol' => $symbol,
				                        'backtrace' => debug_backtrace());
			}

			// Log error to log engine
			$logline = $description.' in '.$file.' on line '.$line;

			switch($code){
				case E_ERROR:
					Logger::error($logline);
					break;
				case E_RECOVERABLE_ERROR:
					Logger::error($logline);
					break;
				case E_STRICT:
					Logger::warning($logline);
					break;
				case E_WARNING:
					Logger::warning($logline);
					break;
				case E_NOTICE:
					Logger::notice($logline);
					break;
				case E_USER_ERROR:
					Logger::error($logline);
					break;
				case E_USER_WARNING:
					Logger::warning($logline);
					break;
				case E_USER_NOTICE:
					Logger::notice($logline);
					break;
				case E_DEPRECATED:
					Logger::warning($logline);
					break;
				case E_USER_DEPRECATED:
					Logger::warning($logline);
					break;
				default:
					Logger::warning($logline);
					break;
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
	public function fatal($buffer=''){
		if(!strstr($buffer, ini_get('error_prepend_string'))){
			// if(!stristr($buffer, '<b>Fatal error</b>:') && !stristr($buffer, '<b>Catchable fatal error</b>:')){
			return false;
		} else {
			// preg_match_all('/\<br \/\>\s\<b\>(.*?)\<\/b\>:\s*(.*?)\sin\s.*?\<b\>(.*?)\<\/b\>\s*on\s*line\s*\<b\>(.*?)<\/b\>\<br \/\>/s', $buffer, $result);
			preg_match_all('/'.ini_get('error_prepend_string').'\n(.*?):(.*?)\s+in\s+(.*?)on\s+line\s+([0-9]+)/s', $buffer, $result);
			// $e = error_get_last();
			while(list($key, $val) = each($result[0])){
				if(preg_match('/^Uncaught exception/', trim($result[2][$key]))){
					$this->trigger(E_USER_ERROR, trim($result[2][$key]), '<pre>'.trim($result[3][$key]), trim($result[4][$key]));
				} else {
					$this->trigger(E_USER_ERROR, trim($result[2][$key]), trim($result[3][$key]), trim($result[4][$key]));
				}
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
				$headers = array('Content-Type: text/html');

				$subject = '';
				if(isset($_SERVER['SERVER_NAME'])){
					$subject .= '['.$_SERVER['SERVER_NAME'].'] ';
					$headers[] = 'From: '.$_SERVER['SERVER_NAME'].' - Corelib error handler <noreply@'.$_SERVER['SERVER_NAME'].'>';
				}
				$subject .= 'Corelib error - '.$checksum;
				mail(BASE_ADMIN_EMAIL, $subject, $buffer, implode("\n", $headers));
			}
			Logger::critical('Error logged with checksum: '.$checksum);

			if(defined('BASE_ERROR_FATAL_REDIRECT')){
				$buffer = '<html><head><meta http-equiv="refresh" content="0;URL='.BASE_ERROR_FATAL_REDIRECT.'?checksum='.$checksum.'"></head></hmtl>';
			} else {
				$buffer = '<html><head><title>500 Internal Server Error</title></head><body><h1>Internal Server Error</h1>The server encountered an internal error or misconfiguration and was unable to complete your request.<p>ID: '.$checksum.'</p><hr><i><a href="http://www.corelib.org/">Corelib v'.CORELIB_BASE_VERSION.'</a></i></body></html>';
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
		if(php_sapi_name() == 'cli'){
			$buffer = '';
			foreach ($this->errors as $key => $error){
				$buffer .= $this->_getErrorString($key, $error);
			}
			return $buffer;
		} else {
			$template = file_get_contents($this->template);
			$content = preg_replace('/^.*?\!ERROR_TEMPLATE {(.*?)}.*/ms', '\\1', $template);

			$buffer = '';
			foreach ($this->errors as $key => $error){
				$buffer .= $this->_getError($key, $error, $content);
			}

			$template = preg_replace('/^(.*?)\!ERROR_TEMPLATE {.*?}(.*?)$/ms', '\\1 '.$buffer.' \\3', $template);
			return $template;
		}
	}


	/**
	 * Merge error template with error content.
	 *
	 * @param array $error error information
	 * @param string $content template
	 * @return string
	 * @internal
	 */
	private function _getError($id, array &$error, $content){
		$return = str_replace('!ERROR_NAME!', $error['code'].': '.$this->_getErrorCodeDescription($error['code']), $content);
		$return = str_replace('!ERROR_DESC!', $error['description'], $return);
		$return = str_replace('!ERROR_FILE!', $error['file'], $return);
		$return = str_replace('!ERROR_LINE!', $error['line'], $return);
		$return = str_replace('!ERROR_FILE_CONTENT!', $this->_getSource($error), $return);
		$return = str_replace('!ERROR_FILE_CONTENT_ID!', 'error-file-content-id-'.$id, $return);
		$return = str_replace('!CORELIB_VERSION!', CORELIB_BASE_VERSION, $return);
		$return = str_replace('!STACK_TRACE!', $this->getTraceAsHTML($error['backtrace']), $return);
		$return = str_replace('!STACK_TRACE_ID!', 'stack-trace-'.$id, $return);
		$return = str_replace('!REQUEST_CONTENT!', $this->_getRequestContentAsHTML(), $return);
		$return = str_replace('!REQUEST_CONTENT_ID!', 'request-content-id-'.$id, $return);
		return $return;
	}

	/**
	 * Get plaintext error description.
	 *
	 * @param array $error error information
	 * @param string $content template
	 * @return string
	 * @internal
	 */
	private function _getErrorString($id, array &$error){
		$return  = $this->_getErrorCodeDescription($error['code']).': ';
		$return .= $error['description'].' in ';
		$return .= $error['file'].' on line '.$error['line']."\n";
		$return .= $this->getTraceAsString($error['backtrace'])."\n";

		/*		$return = str_replace('!ERROR_DESC!', $error['description'], $return);
				$return = str_replace('!ERROR_FILE!', $error['file'], $return);
				$return = str_replace('!ERROR_LINE!', $error['line'], $return);
				$return = str_replace('!ERROR_FILE_CONTENT!', $this->_getSource($error), $return);
				$return = str_replace('!ERROR_FILE_CONTENT_ID!', 'error-file-content-id-'.$id, $return);
				$return = str_replace('!CORELIB_VERSION!', CORELIB_BASE_VERSION, $return);
				$return = str_replace('!CORELIB_COPYRIGHT!', CORELIB_COPYRIGHT, $return);
				$return = str_replace('!CORELIB_COPYRIGHT_YEAR!', CORELIB_COPYRIGHT_YEAR, $return);
				$return = str_replace('!STACK_TRACE!', $this->getTraceAsHTML($error['backtrace']), $return);
				$return = str_replace('!STACK_TRACE_ID!', 'stack-trace-'.$id, $return);
				$return = str_replace('!REQUEST_CONTENT!', $this->_getRequestContentAsHTML(), $return);
				$return = str_replace('!REQUEST_CONTENT_ID!', 'request-content-id-'.$id, $return);
		*/
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
				return 'Fatal error';
				break;
			case E_USER_WARNING:
				return 'Warning';
				break;
			case E_WARNING:
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

				$fileinfo = '';
				if(isset($level['file'])){
					$fileinfo .= $level['file'];
				}
				if(isset($level['line'])){
					$fileinfo .= ':'.$level['line'];
				}

				$return .= '#'.($key + 1).' '.$fileinfo.' '.htmlspecialchars($function).'('.implode(', ', $args).')'."<br/>";
			}
		}
		return $return;
	}

	/**
	 * Get trace as plaintext.
	 *
	 * @param array $trace
	 * @return string
	 */
	public function getTraceAsString(array &$trace){
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
						$args[] = '\''.substr($arg, 0, 30).'\'';
					} else {
						$args[] = $arg;
					}
				}

				$fileinfo = '';
				if(isset($level['file'])){
					$fileinfo .= $level['file'];
				}
				if(isset($level['line'])){
					$fileinfo .= ':'.$level['line'];
				}

				$return .= '#'.($key + 1).' '.$fileinfo.' '.$function.'('.implode(', ', $args).')'."\n";
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
			if(is_array($data)){
				$data = 'Array';
			}
			$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
		}
		$return .= '</table>';
		if(isset($_GET) && sizeof($_GET) > 0){
			$return .= '<br/><b>$_GET</b><table>';
			foreach($_GET as $key => $data){
				$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
			}
			$return .= '</table>';
		}
		if(isset($_POST) && sizeof($_POST) > 0){
			$return .= '<br/><b>$_POST</b><table>';
			foreach($_POST as $key => $data){
				$return .= '<tr><td style="font-size: 10px;">'.htmlspecialchars($key).'</td><td style="font-size: 10px; padding-right: 10px; padding-left: 10px;">=</td><td style="font-size: 10px;"> '.htmlspecialchars($data).'</td></tr>';
			}
			$return .= '</table>';
		}
		return $return;
	}
}
?>