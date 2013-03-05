<?php
namespace Corelib\Base\Input;

use Corelib\Base\ServiceLocator\Service, Corelib\Base\ServiceLocator\Autoloadable;
use Corelib\Base\PageFactory\Output;

/**
 * Session handler.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class Handler implements Service,Output,Autoloadable {


	//*****************************************************************//
	//**************** InputHandler class properties ******************//
	//*****************************************************************//
	/**
	 * @var array valid post variables
	 * @internal
	 */
	private $post_valid = array();

	/**
	 * @var array post variables
	 * @internal
	 */
	private $post = array();

	/**
	 * @var array valid get variables
	 * @internal
	 */
	private $get_valid = array();

	/**
	 * @var array get variables
	 * @internal
	 */
	private $get = array();

	/**
	 * @var boolean true if a error occured, else false
	 * @internal
	 */
	private $input_error = false;

	/**
	 * @var array get variable error descriptions or codes
	 * @internal
	 */
	private $error_overwrite_get = array();

	/**
	 * @var array post variable error descriptions or codes
	 * @internal
	 */
	private $error_overwrite_post = array();

	/**
	 * @var array get variables which should be stripped
	 * @internal
	 */
	private $serialize_strip_get = array();

	/**
	 * @var array post variables which should be stripped
	 * @internal
	 */
	private $serialize_strip_post = array();


	//*****************************************************************//
	//****************** InputHandler class methods *******************//
	//*****************************************************************//
	/**
	 * Input handler constructor.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		if(!defined('INPUT_HANDLER_RESET_GET_POST')){
			/**
			 * Reset get and post variables
			 *
			 * If this constant is set to true the input handler
			 * will automatically remove the content of $_GET and $_POST.
			 * This is done by default, and to disable this behavior set this
			 * contant to false in your config or abstracts file.
			 *
			 * @var boolean
			 */
			define('INPUT_HANDLER_RESET_GET_POST', true);
		}

		// XXX Added check, due to php 5.3 deprecation of set_magic_quotes_runtime()
		if(version_compare(PHP_VERSION, '5.3') == -1){
			set_magic_quotes_runtime(false);
		}

		if(isset($_POST)){
			$this->post = &$_POST;
		}
		if(isset($_GET)){
			$this->get = &$_GET;
		}
		if(INPUT_HANDLER_RESET_GET_POST){
			unset($_POST, $_GET);
		}

		if(get_magic_quotes_gpc()){
			$this->get = $this->_stripslashes($this->get);
			$this->post = $this->_stripslashes($this->post);
		}
	}

	/**
	 * @return Handler
	 */
	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get(__CLASS__);
	}

	/**
	 * Validate post variable.
	 *
	 * Apply {@link InputValidator} object to post variable.
	 *
	 * @param string $item post variable name
	 * @param InputValidator $mode
	 * @return boolean true if valid, else return false
	 * @see InputHandler::unValidatePost()
	 * @see InputHandler::isValidPost()
	 * @see InputHandler::isSetPost()
	 * @see InputHandler::setPost()
	 * @see InputHandler::unsetPost()
	 * @see InputHandler::validateGet()
	 */
	public function validatePost($item, Validator $mode){
		if(isset($this->post[$item])){
			if($valid = $this->_validate($this->post[$item], $mode)){
				$this->post_valid[$item] = &$this->post[$item];
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

	/**
	 * Validate get variable.
	 *
	 * Apply {@link InputValidator} object to get variable.
	 *
	 * @param string $item get variable name
	 * @param InputValidator $mode
	 * @return boolean true if valid, else return false
	 * @see InputHandler::unValidateGet()
	 * @see InputHandler::isValidGet()
	 * @see InputHandler::isSetGet()
	 * @see InputHandler::setGet()
	 * @see InputHandler::unsetGet()
	 * @see InputHandler::validatePost()
	 */
	public function validateGet($item, Validator $mode){
		if(isset($this->get[$item]) && $valid = $this->_validate($this->get[$item], $mode)){
			$this->get_valid[$item] = &$this->get[$item];
			return $valid;
		} else {
			if(isset($this->get_valid[$item])){
				unset($this->get_valid[$item]);
			}
			$this->input_error = true;
			return false;
		}
	}

	/**
	 * Force invalid get variable.
	 *
	 * force a valid get variable into being a invalid get variable.
	 *
	 * @param string $item get variable name
	 * @return boolean true if variable is valid and action was successfull, else return false
	 * @see InputHandler::validateGet()
	 */
	public function unValidateGet($item){
		if(isset($this->get_valid[$item])){
			unset($this->get_valid[$item]);
			return true;
		}
		return false;
	}

	/**
	 * Force invalid get variable.
	 *
	 * force a valid get variable into being a invalid get variable.
	 *
	 * @param string $item get variable name
	 * @return boolean true if variable is valid and action was successfull, else return false
	 * @see InputHandler::validatePost()
	 */
	public function unValidatePost($item){
		if(isset($this->post_valid[$item])){
			unset($this->post_valid[$item]);
			return true;
		}
		return false;
	}

	/**
	 * Check to see if a get variable is set.
	 *
	 * @param string $item get variable name
	 * @return boolean true if set else return false
	 */
	public function isSetGet($item){
		return isset($this->get[$item]);
	}

	/**
	 * Check to see if a post variable is set.
	 *
	 * @param string $item post variable name
	 * @return boolean true if set else return false
	 */
	public function isSetPost($item){
		return isset($this->post[$item]);
	}

	/**
	 * Check to see if a get variable is valid.
	 *
	 * @param string $item get variable name
	 * @return boolean true if set else return false
	 */
	public function isValidGet($item){
		return isset($this->get_valid[$item]);
	}

	/**
	 * Check to see if a post variable is valid.
	 *
	 * @param string $item get variable name
	 * @return boolean true if set else return false
	 */
	public function isValidPost($item){
		return isset($this->post_valid[$item]);
	}

	/**
	 * Get a valid get variable.
	 *
	 * @param string $item get variable name
	 * @param Converter $converter set value converter
	 * @return mixed variable value if variable is valid else return false
	 * @throws BaseInputHandlerInvalidGetException
	 * @since Version 5.0 the $converter parameter became available
	 */
	public function getGet($item, Converter $converter=null){
		if(!isset($this->get_valid[$item])){
			throw new BaseInputHandlerInvalidGetException($item);
			return false;
		} else if(!is_null($converter)){
			return $converter->convert($this->get_valid[$item]);
		} else {
			return $this->get_valid[$item];
		}
	}

	/**
	 * Get a valid post variable.
	 *
	 * @param string $item post variable name
	 * @param Converter $converter set value converter
	 * @return mixed variable value if variable is valid else return false
	 * @throws BaseInputHandlerInvalidPostException
	 * @since Version 5.0 the $converter parameter became available
	 */
	public function getPost($item, Converter $converter=null){
		if(!isset($this->post_valid[$item])){
			throw new BaseInputHandlerInvalidPostException($item);
		} else if(!is_null($converter)){
			return $converter->convert($this->post_valid[$item]);
		} else {
			return $this->post_valid[$item];
		}
		return false;
	}

	/**
	 * Set get variable value.
	 *
	 * Set a get variable value and mark it as valid.
	 *
	 * @param string $item get variable name
	 * @param mixed $value get value
	 * @return boolean true on success, else return false
	 */
	public function setGet($item, $value){
		$this->get_valid[$item] = $value;
		$this->get[$item] = $value;
		return true;
	}

	/**
	 * Set post variable value.
	 *
	 * Set a post variable value and mark it as valid.
	 *
	 * @param string $item post variable name
	 * @param mixed $value post value
	 * @return boolean true on success, else return false
	 */
	public function setPost($item, $value){
		$this->post_valid[$item] = $value;
		$this->post[$item] = $value;
	}

	/**
	 * Unset post variable.
	 *
	 * @param string $item post variable name
	 * @return boolean true on success, else return false
	 */
	public function unsetPost($item){
		$item = func_get_args();
		while (list(,$val) = each($item)){
			unset($this->post_valid[$val], $this->post[$val]);
		}
	}

	/**
	 * Unset get variable.
	 *
	 * @param string $item get variable name
	 * @return boolean true on success, else return false
	 */
	public function unsetGet($item){
		$item = func_get_args();
		while (list(,$val) = each($item)){
			unset($this->get_valid[$val], $this->get[$val]);
		}
	}


	/**
	 * 	Serialize get array.
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
						$encoded .= $key.'-error='.urlencode($error).'&';
					} else {
						$encoded[$key.'_error'] = $error;
					}
				} else {
					if($urlencode){
						$encoded .= $key.'-error&';
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
	 * 	Serialize Post Variables.
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
	 * 	Serialize Get Variables.
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
	 * 	Set Post Error Code.
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
	 * 	Set Get Error Code.
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
	 * 	Strip variable from get array when serializing.
	 *
	 * 	@param string $item get variable name
	 * 	@return boolean true
	 */
	public function addStripSerializeGetVariable($item){
		$this->serialize_strip_get[$item] = true;
		return true;
	}

	/**
	 * 	Strip variable from post array when serializing.
	 *
	 * 	@param string $item post variable name
	 * 	@return boolean true
	 */
	public function addStripSerializePostVariable($item){
		$this->serialize_strip_post[$item] = true;
		return true;
	}

	/**
	 *	Check multiple get variables for validity.
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
	 *	Check multiple post variables for validity.
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


	/**
	 * Get XML content.
	 *
	 * @see Output::getXML()
	 */
	public function getXML(\DOMDocument $xml){
		$XMLget = $xml->createElement('get');
		while(list($key, $val) = each($this->get)){
			if(preg_match('/^[a-zA-Z0-9_-]*$/', $key)){
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

	/**
	 * Translate array into XML tree.
	 *
	 * @param DOMDocument $xml
	 * @param DOMElement $parentNode
	 * @param array $array
	 * @return void
	 * @internal
	 */
	private function _xmlArray(DOMDocument $xml, DOMElement $parentNode, array $array){
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

	/**
	 * Stripslashes from value.
	 *
	 * @param mixed $subject
	 * @return mixed stripslashed value
	 * @internal
	 */
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

	/**
	 * URL encode value.
	 *
	 * @param string $subject
	 * @return string urlencoded value
	 * @internal
	 */
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

	/**
	 * URL encode array recursive.
	 *
	 * @param mixed $array
	 * @param mixed $parent parent value.
	 * @return string url encoded array
	 * @internal
	 */
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

	/**
	 * Generic value validation
	 *
	 * Match value against {@link InputValidator}
	 *
	 * @param mixed $content value
	 * @param InputValidator $mode
	 * @return boolean true if valid, else return false
	 * @internal
	 */
	private function _validate($content, Validator $mode){
		return $mode->validate($content);
	}
}
?>