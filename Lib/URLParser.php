<?php
class URLParser implements Singleton {
	private static $instance = null;
	
	private $url_parts = array();
	private $url = null;
	private $query = null;
	
	private function __construct(){
		if(!isset($_GET['page'])){
			if(strstr($_SERVER['REQUEST_URI'], '?')){
				list($this->url, $this->query) = explode('?', $_SERVER['REQUEST_URI']);
			} else {
				$this->url = $_SERVER['REQUEST_URI'];
			}
		} else {
			$this->url = $_GET['page'];
		}
		$this->_setUrlParts();
	}
	
	private function _setUrlParts(){
		$parse_url = preg_replace('/^\//','', $this->url);
		$parse_url = preg_replace('/\/$/','', $parse_url);
		$this->url_parts = explode('/', $parse_url);
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
			if(preg_match('/^[a-z0-9A-Z]*$/', $val)){
				$part = $urlparts->appendChild($xml->createElement('urlpart', $val));
				$part->setAttribute('id', $key);
			}
		}
		reset($this->url_parts);
		return $urlparts;
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
}
?>