<?php
/*	@version 1.0 Beta ($Id: EventHandler.php 130 2005-10-08 00:44:20Z wayland $) */

interface InputValidator {
	public function validate($content);
}

class InputHandler implements Singleton,Output {
	private static $instance = null;
	
	private $post_valid = array();
	private $post = array();
	private $get_valid = array();
	private $get = array();
	private $addslashes = true;
	private $input_error = false;
	private $error_overwrite_get = array();
	private $error_overwrite_post = array();
	private $serialize_strip_get = array();
	private $serialize_strip_post = array();
	
	private function __construct(){
		if(!defined('INPUT_HANDLER_RESET_GET_POST')){
			define('INPUT_HANDLER_RESET_GET_POST', true);
		}
		set_magic_quotes_runtime(FALSE);
		if (get_magic_quotes_gpc()) {		
			$this->addslashes = false;
		} else {
			$this->addslashes = true;
		}
		
		if(isset($_POST)){
			$this->post = $_POST;
			if(INPUT_HANDLER_RESET_GET_POST){
				unset($_POST);
			}
		}

		if(isset($_GET)){
			$this->get = $_GET;
			if(INPUT_HANDLER_RESET_GET_POST){
				unset($_GET);
			}
		}
	}
	
	public function addslashes($boolean=true){
		$this->addslashes = $boolean;	
	}
	
	public function validatePost($item, InputValidator $mode){
		if(isset($this->post[$item])){
			if($valid = $this->_validate($this->post[$item], $mode)){
				$this->post_valid[$item] = &$this->post[$item];
				if($this->addslashes){
					if(is_array($this->post_valid[$item])) {
						foreach ($this->post_valid[$item] as $key=>$val) {
							$this->post_valid[$item][$key] = addslashes($this->post_valid[$item][$key]);
						}
					} else {
						$this->post_valid[$item] = addslashes($this->post_valid[$item]);
					}
				}
				return $valid;
			} else {
				if(isset($this->post_valid[$item])){
					unset($this->post_valid[$item]);
				}
				$this->input_error = true;
				return false;
			}
		} else {
			$this->input_error = true;
			return false;
		}
	}

	public function validateGet($item, InputValidator $mode){
		if(isset($this->get[$item]) && $valid = $this->_validate($this->get[$item], $mode)){
			$this->get_valid[$item] = $this->get[$item];
			if($this->addslashes){
				$this->get_valid[$item] = addslashes($this->get_valid[$item]);
			}
			return $valid;
		} else {
			if(isset($this->get_valid[$item])){
				unset($this->get_valid[$item]);
			}			
			$this->input_error = true;
			return false;
		}
	}
	
	public function unValidateGet($item){
		if(isset($this->get_valid[$item])){
			unset($this->get_valid[$item]);
			return true;
		}
		return false;
	}
	
	public function unValidatePost($item){
		if(isset($this->post_valid[$item])){
			unset($this->post_valid[$item]);
			return true;
		}
		return false;
	}
	
	private function _validate($content, InputValidator $mode){
		return $mode->validate($content);
	}

	public function isSetGet($item){
		return isset($this->get[$item]);	
	}

	public function isSetPost($item){
		return isset($this->post[$item]);	
	}
	
	public function isValidGet($item){
		return isset($this->get_valid[$item]);	
	}

	public function isValidPost($item){
		return isset($this->post_valid[$item]);	
	}
	
	public function getGet($item, $specialchars=true){
		try {
			if(!isset($this->get_valid[$item])){
				throw new BaseException('Variable Not valid');	
			} else {
				if($specialchars){
					return htmlspecialchars($this->get_valid[$item], ENT_COMPAT, 'UTF-8');
				} else {
					return $this->get_valid[$item];
				}
			}
		} catch (Exception $e){
			echo 'An unvalidated GET variable was requested: '.$item, $e;
			echo "\n\n";
		}
		return false;
	}
	
	public function getPost($item, $specialchars=true){
		try {
			if(!isset($this->post_valid[$item])){
				throw new BaseException('Variable Not valid');	
			} else {
				if($specialchars){
					return htmlspecialchars($this->post_valid[$item], ENT_COMPAT, 'UTF-8');
				} else {
					return $this->post_valid[$item];
				}
			}
		} catch (Exception $e){
			echo 'An unvalidated POST variable was requested: '.$item, $e;
			echo "\n\n";
		}
		return false;
	}
	public function setGet($item,$value){	
		$this->get_valid[$item] = $value;
		$this->get[$item] = $value;
	}
	public function setPost($item,$value){
		$this->post_valid[$item] = $value;
		$this->post[$item] = $value;
	}
	
	/**
	 *	@return InputHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new InputHandler();
		}
		return self::$instance;	
	}
	
	public function dumpPost(){
		return serialize($this->post_valid);
	}
	public function dumpGet(){
		return serialize($this->get_valid);
	}
	
	public function importPostDump($dump){
		$this->post_valid = array_merge($this->post_valid, unserialize($dump));
		$this->post = array_merge($this->post, unserialize($dump));
	}
	public function importGetDump($dump){
		$this->get_valid = array_merge($this->get_valid, unserialize($dump));
		$this->get = array_merge($this->get, unserialize($dump));
	}
	
	/**
	 * 	Serialize get array
	 * 
	 * 	Serialize get Array, and return seriliazed string, with embedded error
	 * 	codes.
	 *
	 * 	@param array $array array holding get or post variables
	 * 	@param array $valid array holding valid get or post variables
	 * 	@param array $error_codes array holding error code overwrites
	 * 	@param array $strip_variables array holding strip override variables
	 * 	@param boolean $urlencode if true, return urlencoded string else return serialized string
	 * 	@return string serialized string
	 */
	private function _serializeArray(&$array, &$valid, &$error_codes, &$strip_variables, $urlencode=true){
		$encoded = '';
		while(list($key, $val) = each($array)){
			if(!empty($val) && !isset($strip_variables[$key])){
				if($urlencode){
					if($this->addslashes){
						$val = stripslashes($val);
					}
					$encoded .= $key.'='.urlencode($val).'&';
				} else {
					$encoded[$key] = $val;
				}
			}
			if(!isset($valid[$key])){
				if(isset($error_codes[$key])){
					if($urlencode){
						$error = $error_codes[$key];
						$encoded .= $key.'_error='.urlencode($error).'&';
					} else {
						$encoded[$key.'_error'] = $error;
					}
				} else {
					if($urlencode){
						$encoded .= $key.'_error&';
					} else {
						$encoded[$key.'_error'] = true;
					}
				}
			}
			if(!$urlencode){
				$encoded = serialize($encoded);
			}
		}
		return $encoded;
	}
	
	/**
	 * 	Serialize Post Variables
	 * 
	 *	if $urlencode is set true a url encoded string is returned
	 * 	otherwise a serialized array is returned
	 *
	 * 	@uses InputHandler::_serializeArray()
	 * 	@param boolean $urlencode if true, variables are url encoded
	 * 	@return string serialized string
	 */
	public function serializePost($urlencode = true){
		return $this->_serializeArray($this->post, $this->post_valid, $this->error_overwrite_post, $this->serialize_strip_post, $urlencode);
	}
	
	/**
	 * 	Serialize Get Variables
	 * 
	 *	if $urlencode is set true a url encoded string is returned
	 * 	otherwise a serialized array is returned
	 *
	 * 	@uses InputHandler::_serializeArray()
	 * 	@param boolean $urlencode if true, variables are url encoded
	 * 	@return string serialized string
	 */	
	public function serializeGet($urlencode = true){
		return $this->_serializeArray($this->get, $this->get_valid, $this->error_overwrite_get, $this->serialize_strip_get, $urlencode);
	}
	
	/**
	 * 	Set Post Error Code
	 * 
	 *	This is used together with {@link InputHandler::serializePost()},
	 *  embedding error codes within the serialised string.
	 *
	 * 	@see InputHandler::serializePost()
	 * 	@param boolean $urlencode if true, variables are url encoded
	 * 	@return boolean true
	 */	
	public function setPostErrorCode($item, $code){
		$this->error_overwrite_post[$item] = $code;
		return true;
	}
	
	/**
	 * 	Set Get Error Code
	 * 
	 *	This is used together with {@link InputHandler::serializeGet()},
	 *  embedding error codes within the serialised string.
	 *
	 * 	@see InputHandler::serializeGet()
	 * 	@param boolean $urlencode if true, variables are url encoded
	 * 	@return boolean true
	 */	
	public function setGetErrorCode($item, $code){
		$this->error_overwrite_get[$item] = $code;
		return true;
	}
	
	/**
	 * 	Strip variable from get array when serializing
	 *
	 * 	@param string $item get variable name
	 * 	@return boolean true
	 */
	public function addStripSerializeGetVariable($item){
		$this->serialize_strip_get[$item] = true;
		return true;
	}
	/**
	 * 	Strip variable from post array when serializing
	 *
	 * 	@param string $item post variable name
	 * 	@return boolean true
	 */
	public function addStripSerializePostVariable($item){
		$this->serialize_strip_post[$item] = true;
		return true;
	}
	
	/**
	 *	Check multiple get variables for validity
	 * 
	 * 	If no variable names is added, all variables will be checked,
	 * 	this function can take any number of get variables as arguments
	 *	
	 *	@return boolean true, if all get variables is valid, else return false
	 */
	public function isValidGetVariables($item1=null, $item2=null, $item3=null){
		$array = func_get_args();
		if(sizeof($array) > 0){
			while(list(,$val) = each($array())){
				if(!$this->isValidGet($val)){
					return false;
				}
			}
		} else {
			while(list($key,$val) = each($this->get)){
				if(!$this->isValidPost($key)){
					reset($this->get);
					return false;
				}
			}
			reset($this->get);
		}
		return true;
	}

	/**
	 *	Check multiple post variables for validity
	 * 
	 * 	If no variable names is added, all variables will be checked,
	 * 	this function can take any number of post variables as arguments
	 *	
	 *	@return boolean true, if all post variables is valid, else return false
	 */
	public function isValidPostVariables($item1=null, $item2=null, $item3=null){
		$array = func_get_args();
		if(sizeof($array) > 0){
			while(list(,$val) = each($array)){
				if(!$this->isValidPost($val)){
					return false;
				}
			}
		} else {
			while(list($key,$val) = each($this->post)){
				if(!$this->isValidPost($key)){
					reset($this->post);
					return false;
				}
			}
			reset($this->post);
		}
		return true;
	}
	
	
	public function getXML(DOMDocument $xml){	
		$XMLget = $xml->createElement('get');
		while(list($key, $val) = each($this->get)){
			if(preg_match('/^[a-zA-Z0-9_]*$/', $key)){
				if(is_array($val)) {
					$XMLArray = $xml->createElement($key);
					
					while(list($key2, $val2) = each($val)){
						$XMLItem = $xml->createElement('item', $val2);
						$XMLItem->setAttribute('id',$key2);
						$XMLArray->appendChild($XMLItem);
					}
					$XMLget->appendChild($XMLArray);
				} else {
					$XMLget->appendChild($xml->createElement($key, $val));
				}
			}
		}
		reset($this->get);
		return $XMLget;
	}
	
	public function &getArray(){ }
	public function getString($format = '%1$s'){ }
}

class RegexInputValidator implements InputValidator {
	private $expr;
	
	public function __construct($expr){
		$this->expr = $expr;
	}
	
	public function validate($content){
		return preg_match($this->expr, $content);
	}	
}

class EmailInputValidator implements InputValidator {
	public function validate($content){
       	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $content));
	}	
}
class UrlInputValidator implements InputValidator {
	public function validate($content){
		return(preg_match("/^(http|https|ftp):\/\/[a-z0-9\/:_\-_\.\?\$,~=#&%\+]+$/i",$content));
	}
}

class EqualsInputValidator implements InputValidator {
	private $expr;
	
	public function __construct($expr){
		$this->expr = $expr;
	}
	
	public function validate($content){
		return ($this->expr == $content);
	}
}

class IsArrayInputValidator implements InputValidator {
	public function validate($content){
		return (is_array($content));
	}
}

class ArrayInputValidator implements InputValidator {
	private $validator;
	
	public function __construct($validator){
		$this->validator = $validator;
	}
	public function validate($content){
		foreach ($content as $k => $v) {
			if(!$this->validator->validate($content[$k])) {
				return false;
			}
		}
		return true;
	}
}

?>