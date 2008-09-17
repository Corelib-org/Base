<?php
/*	@version 1.0 Beta ($Id$) */

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

	/**
	 * @var array
	 */
	private $cp1252_bad_charecters = array("\xE2\x80\xA6",        // ellipsis
	                                       "\xE2\x80\x93",        // long dash
	                                       "\xE2\x80\x94",        // long dash
	                                       "\xE2\x80\x98",        // single quote opening
	                                       "\xE2\x80\x99",        // single quote closing
	                                       "\xE2\x80\x9c",        // double quote opening
	                                       "\xE2\x80\x9d",        // double quote closing
	                                       "\xE2\x80\xa2");       // dot used for bullet points)
	/**
	 * @var array
	 */
	private $cp1252_new_charecters = array('...',
	                                       '-',
	                                       '-',
	                                       '\'',
	                                       '\'',
	                                       '"',
	                                       '"',
	                                       '*');

	private function __construct(){
		if(!defined('INPUT_HANDLER_RESET_GET_POST')){
			define('INPUT_HANDLER_RESET_GET_POST', true);
		}
		set_magic_quotes_runtime(false);

		if(isset($_POST)){
			$this->post = $_POST;
		}
		if(isset($_GET)){
			$this->get = $_GET;
		}
		if(INPUT_HANDLER_RESET_GET_POST){
			unset($_POST, $_GET);
		}
		if(get_magic_quotes_gpc()){
			$this->get = $this->_stripslashes($this->get);			
			$this->post = $this->_stripslashes($this->post);			
		}
		$this->addslashes = true;
	}

	public function addslashes($boolean=null){
		if(!is_null($boolean)){
			$this->addslashes = $boolean;
		}
		return $this->addslashes;
	}

	public function validatePost($item, InputValidator $mode){
		if(isset($this->post[$item])){
			if($valid = $this->_validate($this->post[$item], $mode)){
				$this->post[$item] = $this->_cp1252Safe($this->post[$item]);
				$this->post_valid[$item] = &$this->post[$item];
				if($this->addslashes){
					$this->post_valid[$item] = $this->_addslashes($this->post_valid[$item]);
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
			$this->post[$item] = null;
			$this->input_error = true;
			return false;
		}
	}

	public function validateGet($item, InputValidator $mode){
		if(isset($this->get[$item]) && $valid = $this->_validate($this->get[$item], $mode)){
			$this->get[$item] = $this->_cp1252Safe($this->get[$item]);
			$this->get_valid[$item] = &$this->get[$item];
			if($this->addslashes){
				$this->get_valid[$item] = $this->_addslashes($this->get_valid[$item]);
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

	public function getGet($item, $specialchars=false){
		try {
			if(!isset($this->get_valid[$item])){
				throw new BaseException('Variable Not valid');
			} else {
				if($specialchars){
					return $this->_htmlspecialchars($this->get_valid[$item], ENT_COMPAT, 'UTF-8');
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

	public function getPost($item, $specialchars=false){
		try {
			if(!isset($this->post_valid[$item])){
				throw new BaseException('Variable Not valid');
			} else {
				if($specialchars){
					return $this->_htmlspecialchars($this->post_valid[$item], ENT_COMPAT, 'UTF-8');
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

	public function unsetPost($item){
		$item = func_get_args();
		while (list(,$val) = each($item)){
			unset($this->post_valid[$val], $this->post[$val]);
		}
	}
	public function unsetGet($item){
		$item = func_get_args();
		while (list(,$val) = each($item)){
			unset($this->get_valid[$val], $this->get[$val]);
		}
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
			if(!isset($strip_variables[$key])){
				if($urlencode){
					if($this->addslashes){
						$val = $this->_stripslashes($val);
					}
					$val = $this->_urlencode($val);
					$encoded .= $this->_urlencodeArray($val, $key).'&';
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
		while(strstr($encoded, '&&')){
			$encoded = str_replace('&&', '&', $encoded);
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
	public function isValidGetVariables($item=null /*, [$items...] */){
		if(!is_array($item)){
			$array = func_get_args();
		} else {
			$array = $item;
		}
		
		if(sizeof($array) > 0){
			while(list(,$val) = each($array)){
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
	public function isValidPostVariables($item=null /*, [$items...] */){
		if(!is_array($item)){
			$array = func_get_args();
		} else {
			$array = $item;
		}
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
					$this->_xmlArray($xml, $XMLArray, $val);
					$XMLget->appendChild($XMLArray);
				} else {
					$XMLget->appendChild($xml->createElement($key, $val));
				}
			}
		}
		reset($this->get);
		return $XMLget;
	}

	public function &getArray(){
		$array = array();
		while(list($key, $val) = each($this->get)){
			if(preg_match('/^[a-zA-Z0-9_]*$/', $key)){
				if(is_array($val)) {
					$array[$key] = $val;
				} else {
					$array[$key] = $val;
				}
			}
		}
		$array = array('get'=>$array);
		return $array;
	}


	private function _xmlArray(DOMDocument $xml, DOMElement $parentNode, $array){
		while(list($key, $val) = each($array)){
			if(is_array($val)){
				$XMLItem = $xml->createElement('item');
				$this->_xmlArray($xml, $XMLItem, $val);
			} else {
				$XMLItem = $xml->createElement('item', $val);
			}
			$XMLItem->setAttribute('id',$key);
			$parentNode->appendChild($XMLItem);
		}

	}
	private function _addslashes($subject){
		if(is_array($subject)){
			while(list($key,$val) = each($subject)){
				$subject[$key] = $this->_addslashes($val);
			}
			reset($subject);
		} else {
			$subject = addslashes($subject);
		}
		return $subject;
	}
	private function _stripslashes($subject){
		if(is_array($subject)){
			while(list($key,$val) = each($subject)){
				$subject[$key] = $this->_stripslashes($val);
			}
			reset($subject);
		} else {
			$subject = stripslashes($subject);
		}
		return $subject;
	}
	private function _urlencode($subject){
		if(is_array($subject)){
			while(list($key,$val) = each($subject)){
				$subject[$key] = $this->_urlencode($val);
			}
			reset($subject);
		} else {
			$subject = urlencode($subject);
		}
		return $subject;
	}
	private function _urlencodeArray($array, $parent=null){
		$return = '';
		if(is_array($array)){
			while(list($key,$val) = each($array)){
				$return .= '&'.$this->_urlencodeArray($val, $parent.'['.$key.']');
			}
			while(strstr($return, '&&')){
				$return = str_replace('&&', '&', $return);
			}
			return $return;
		} else {
			return $parent.'='.$array;
		}

	}

	private function _cp1252Safe($string){
		return str_replace($this->cp1252_bad_charecters, $this->cp1252_new_charecters, $string);
	}

	private function _htmlspecialchars($subject, $quote_style=ENT_COMPAT, $charset='UTF-8'){
		if(is_array($subject)){
			while(list($key,$val) = each($subject)){
				$subject[$key] = $this->_htmlspecialchars($val, $quote_style, $charset);
			}
			reset($subject);
		} else {
			$subject = htmlspecialchars($subject, $quote_style, $charset);
		}
		return $subject;
	}
}

class InputValidatorRegex implements InputValidator {
	private $expr;

	public function __construct($expr){
		$this->expr = $expr;
	}

	public function validate($content){
		return preg_match($this->expr, $content);
	}
}

/**
 * @deprecated use InputValidatorRegex istead
 */
class RegexInputValidator extends InputValidatorRegex { }

class InputValidatorEmail implements InputValidator {
	public function validate($content){
       	return(preg_match('/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i', $content));
	}
}
/**
 * @deprecated use InputValidatorEmail istead
 */
class EmailInputValidator extends InputValidatorEmail {
	
}

class InputValidatorUrl implements InputValidator {
	public function validate($content){
		return preg_match('/^(http|https|ftp):\/\/[a-z0-9\/:_\-_\.\?\$,~=#&%\+]+$/i',$content);
	}
}
/**
 * @deprecated use InputValidatorUrl istead
 */
class UrlInputValidator extends InputValidatorUrl {
	
}

class InputValidatorPhone implements InputValidator {
	public function validate($content){
		return(preg_match('/^\+?[\-\s0-9]{8,}$/',$content));
	}
}
/**
 * @deprecated use InputValidatorPhone istead
 */
class PhoneInputValidator extends InputValidatorPhone {
	
}

class InputValidatorEquals implements InputValidator {
	private $expr;

	public function __construct($expr){
		$this->expr = $expr;
	}

	public function validate($content){
		return ($this->expr == $content);
	}
}
/**
 * @deprecated use InputValidatorEquals istead
 */
class EqualsInputValidator extends InputValidatorEquals {
	
}

class ArrayInputValidator implements InputValidator {
	private $validator;

	public function __construct($validator=null){
		$this->validator = $validator;
	}
	public function validate($content){
		if(!$this->validator instanceof InputValidator && is_array($content)){
			return true;
		} else {
			foreach ($content as $k => $v) {
				if(!$this->validator->validate($content[$k])) {
					return false;
				}
			}
			return true;
		}
	}
}

/**
 * @deprecated use ArrayInputValidator instead
 */
class IsArrayInputValidator extends ArrayInputValidator { }

class InputValidatorIsSet implements InputValidator {
	public function validate($content){
		$regex = new RegexInputValidator('/^.*$/');
		return $regex->validate($content);
	}
}
/**
 * @deprecated use InputValidatorIsSet instead
 */
class IsSetInputValidator extends InputValidatorIsSet {
	
}

?>