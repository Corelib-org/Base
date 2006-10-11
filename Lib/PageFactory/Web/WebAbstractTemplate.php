<?php
abstract class PageFactoryWebAbstractTemplate extends PageFactoryTemplate {
	private $last_modified = null;
	private $expires = null;
	private $cache_control = null;
	private $pragma = null;
	
	private $content_md5 = null;
	private $content_location = null;
	private $content_type = 'text/html';
	private $content_charset = 'UTF-8';
	private $content_length = null;
	
	private $location = null;
	
	private $script_url = null;
	private $script_uri = null;
	
	private $request_uri = null;
	
	private $remote_addr = null;
	
	private $user_agent = null;
	
	private $server_name = null;

	private $stylesheets = array();
	private $javascripts = array();
	
	const REFERER_VAR = 'PUBLIC_REFERER';
	const MSGID = 'MSGID';
	
	public function __construct(){
		if(!define('HTTP_REDIRECT_BASE')){
			
		}
		$this->script_url = $_SERVER['SCRIPT_URL'];
		$this->script_uri = $_SERVER['SCRIPT_URI'];
		$this->request_uri = $_SERVER['REQUEST_URI'];
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->remote_addr = $_SERVER['REMOTE_ADDR'];
		$this->server_name = $_SERVER['SERVER_NAME'];
	}

	public function setLastModified($timestamp){
		try {
			StrictTypes::isInteger($timestamp);
		} catch (BaseException $e){
			echo $e;
		}
		return $this->last_modified = $timestamp;
	}
	public function setExpire($timestamp){
		try {
			StrictTypes::isInteger($timestamp);
		} catch (BaseException $e){
			echo $e;
		}
		return $this->expires = $timestamp;
	}
	public function setContentType($content_type){
		try {
			StrictTypes::isString($content_type);
		} catch (BaseException $e){
			echo $e;
		}
		return $this->content_type = $content_type;
	}
	public function setContentCharset($charset){
		try {
			StrictTypes::isString($charset);
		} catch (BaseException $e){
			echo $e;
		}
		return $this->content_charset = $charset;
	}

	public function addJavaScript($javascript){
		try {
			StrictTypes::isString($javascript);
		} catch (BaseException $e){
			echo $e;
		}
		$this->javascripts[] = $javascript;
	}
	public function addStyleSheet($stylesheet){
		try {
			StrictTypes::isString($stylesheet);
		} catch (BaseException $e){
			echo $e;
		}
		$this->stylesheets[] = $stylesheet;
	}
	
	public function getJavaScripts(){
		return $this->javascripts;
	}
	public function getStyleSheets(){
		return $this->stylesheets;
	}
	
	public function getScriptUrl(){
		return $this->script_url;
	}
	public function getScriptUri(){
		return $this->script_uri;
	}
	public function getRequestUri(){
		return $this->request_uri;
	}
	public function getUserAgent(){
		return $this->user_agent;
	}
	public function getRemoteAddress(){
		return $this->remote_addr;
	}
	public function getServerName(){
		return $this->server_name;
	}
	public function getContentType(){
		return $this->content_type;
	}
	
	public function init(){
		ob_start();
		return is_null($this->location);
	}
	
	public function cleanup(){
		if(is_null($this->location)){
			header('Content-MD5: '.md5(ob_get_contents()));
	
			header('Content-Location: '. $this->request_uri);
			
			if(is_null($this->content_length)){
				$this->content_length = ob_get_length();
			}
			header('Content-Lenght: '.$this->content_length);
	
			$type = $this->content_type;
			if(!is_null($this->content_charset)){
				$type .= '; charset='.$this->content_charset;
			}
			header('Content-Type: '.$type);
			ob_end_flush();
		} else {
			header('Location: '.$this->location);
			ob_end_clean();
		}
	}
	
}
?>