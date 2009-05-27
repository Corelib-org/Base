<?php
/**
 * @todo move in with PageFactory, maybe this should be deprecated
 */
class URLParser implements Singleton,Output {
	private static $instance = null;
	
	private $url_parts = array();
	private $url = null;
	private $query = null;
	
	private function __construct(){
		if(!isset($_GET[PAGE_FACTORY_GET_TOKEN])){
			if(strstr($_SERVER['REQUEST_URI'], '?')){
				list($this->url, $this->query) = explode('?', $_SERVER['REQUEST_URI']);
			} else {
				$this->url = $_SERVER['REQUEST_URI'];
			}
		} else {
			$this->url = $_GET[PAGE_FACTORY_GET_TOKEN];
		}
		$this->_setUrlParts();
	}
	
	/**
	 *	@return URLParser
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new URLParser();
		}
		return self::$instance;	
	}	
	
	private function _setUrlParts(){
		$parse_url = preg_replace('/^\//','', $this->url);
		$parse_url = preg_replace('/\/$/','', $parse_url);
		$this->url_parts = explode('/', $parse_url);
	}
	
	private function _isValidURLPart($part){
		return true;
		// return preg_match('/^[a-z0-9A-Z\.\s-_\0x00f8]*$/', $part);
	}
	
	public function getUrlPart($part){
		if(isset($this->url_parts[$part])){
			return addslashes($this->url_parts[$part]);
		} else {
			return false;
		}
	}
	
	public function getXML(DOMDocument $xml){
		$urlparts = $xml->createElement('urlparts');
		while(list($key,$val) = each($this->url_parts)){
			if(!empty($val) && $this->_isValidURLPart($val)){
				$part = $urlparts->appendChild($xml->createElement('urlpart', $val));
				$part->setAttribute('id', $key);
			} else {
				echo $val;
			}
		}
		reset($this->url_parts);
		return $urlparts;
	}
	public function &getArray(){
		$urlparts['urlparts'] = array();
		while(list($key,$val) = each($this->url_parts)){
			if($this->_isValidURLPart($val)){
				$urlparts['urlparts'][$key] = $val;
			}
		}
		reset($this->url_parts);
		return $urlparts;
	}

}
?>