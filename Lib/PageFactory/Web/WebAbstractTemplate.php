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
	private $message_id = null;
	
	private $script_url = null;
	private $script_uri = null;
	
	private $request_uri = null;
	
	private $remote_addr = null;
	
	private $user_agent = null;
	
	private $server_name = null;

	private $stylesheets = array();
	private $javascripts = array();
	
	private $http_redirect_base = null;
	
	private $set_referer = true;
	
	const REFERER_VAR = 'PUBLIC_REFERER';
	const MSGID = 'MSGID';
	
	public function __construct(){
		$this->script_url = $_SERVER['SCRIPT_URL'];
		$this->script_uri = $_SERVER['SCRIPT_URI'];
		$this->request_uri = $_SERVER['REQUEST_URI'];
		
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->remote_addr = $_SERVER['REMOTE_ADDR'];
		$this->server_name = $_SERVER['SERVER_NAME'];
		if(!defined('HTTP_REDIRECT_BASE')){
			/**
			 * 	Define Redirect Base URL
			 */
			define('HTTP_REDIRECT_BASE', 'http://'.$this->server_name.'/');
		}
		$this->http_redirect_base = HTTP_REDIRECT_BASE;
	}

	public function init(){
		ob_start();
		return is_null($this->location);
	}
	public function cleanup(){
		$session = SessionHandler::getInstance();
		if(!is_null($this->message_id)){
			$session->set(self::MSGID, $this->message_id); 
		}		
		if($this->set_referer){
			$session->set(self::REFERER_VAR, $this->request_uri);
		}
		
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
	public function setLocation($location){
		try {
			if($location !== true){
				StrictTypes::isString($location);
			}
		} catch (BaseException $e){
			echo $e;
		}
		$this->location = $location;
	}
	public function setMessageID($id){
		try {
			StrictTypes::isInteger($id);
		} catch (BaseException $e){
			echo $e;
		}
		$this->message_id = $id;
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
	/**
	 *	System redirect
	 *	
	 *	This function provides a easy way to redirecting using http
	 *
	 *	@param integer $msgID Current msgID
	 *	@param string $target target URL, if URL is'nt prefixed with http:// the function will add http:// is self.
	 *	@uses StringFilter::ContainsHTTP()
	 *
	static public function redirect($msgID=null, $target=null, $append=false, $query=false){
		$session = SessionHandler::getInstance();
	
		if(!is_null($target) && !$append){
		} else if(isset($_SERVER['HTTP_REFERER']) || $session->check(self::REFERER_VAR)){
			$append_string = $target;
			if(isset($_SERVER['HTTP_REFERER'])){
				$target = $_SERVER['HTTP_REFERER'];
			} else {
				$target = $session->get(self::REFERER_VAR);
			}
			if($append){
				if($query){
					$list = explode('&', $append_string);
					while(list($key, $var) = each($list)){
						if(strstr($var, '=')){
							list($var, $content) = explode('=', $var, 2);
						}
						$target = preg_replace('/(\?.*?)&{0,1}'.preg_quote($var).'\={0,1}.+?(&|$)/', '\\1\\2',$target);
					}
					if(!strstr($target,'?')){
						$target .= '?';
					} else if(substr($target, -1) != '?'){
						$target .= '&';
					}	
					$target .= $append_string; 
				} else {
					$target = preg_replace('/\?.*$/','',$target);
				}
			}
		} else {
			$target = '';
		}
		try {
			if(is_null(self::$redirect_base)){
				throw new BaseException('self::$redirect_base Not set');
			} else {
				if(!StringFilter::ContainsHTTP($target)){
					if(substr($target, 0,1) == '/' && substr(self::$redirect_base, -1) == '/'){
						$target = substr($target,1);
					}
					$target = self::$redirect_base.$target;
				}
				header('Location: '.$target);
				exit;	
			}
		} catch (BaseException $e){
			echo $e;	
		}		
		
		
	}
	*/
}
?>