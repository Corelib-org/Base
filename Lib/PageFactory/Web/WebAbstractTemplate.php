<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Abstract Web Template
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the
 *	Bravura Distribution license that is available through the
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura License and are
 *	unable to obtain it through the web, please send a note to
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 *
 *	@author Steffen Sørensen <steffen@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 *	@version 1.0.0 ($Id$)
 */
if(!defined('BASE_URL')){
	/**
	 * 	Define Redirect Base URL
	 */
	define('BASE_URL', 'http://'.$_SERVER['SERVER_NAME'].'/');

	Base::getInstance()->loadClass('WebInteralLoopbackStream');
}

abstract class PageFactoryWebAbstractTemplate extends PageFactoryTemplate {
	private $last_modified = null;
	private $expires = null;
	private $cache_control = null;
	private $pragma = null;

	private $content_md5 = null;
	private $content_location = null;
	private $content_type = 'text/html';
	private $content_charset = 'UTF-8';

	private $location = null;
	private $message_id = null;

	private $script_url = null;
	private $script_uri = null;

	private $request_uri = null;

	private $http_referer = null;

	private $remote_addr = null;

	private $user_agent = null;

	private $server_name = null;

	private $stylesheets = array();
	private $javascripts = array();

	private $http_redirect_base = null;

	private $set_referer = true;

	private $cache_append = '';

	const REFERER_VAR = 'PUBLIC_REFERER';
	const MSGID = 'MSGID';

	public function __construct(){
		if(!defined('HTTP_STATUS_MESSAGE_FILE')){
			/**
			 * 	Define Redirect Base URL
			 */
			define('HTTP_STATUS_MESSAGE_FILE', 'share/messages.xml');
		}
		if(isset($_SERVER['SCRIPT_URL'])){
			$this->script_url = $_SERVER['SCRIPT_URL'];
		}
		if(isset($_SERVER['SCRIPT_URI'])){
			$this->script_uri = $_SERVER['SCRIPT_URI'];
		}
		$this->request_uri = $_SERVER['REQUEST_URI'];

		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		if(isset($_SERVER['HTTP_REFERER'])){
			$this->http_referer = $_SERVER['HTTP_REFERER'];
		}

		$this->remote_addr = $_SERVER['REMOTE_ADDR'];
		$this->server_name = $_SERVER['SERVER_NAME'];

		$this->http_redirect_base = BASE_URL;
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->set_referer = false;
		}
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
	public function setLocation($location, $param=null){
		if($location{0} != '/' && !preg_match('/^http:/', $location)){
			$location = '/'.$location;
		}
		if(!is_null($param)){
			if(strstr($this->http_redirect_base.$location, '?')){
				$param = '&'.$param;
			} else {
				$param = '?'.$param;
			}
		}
		if(preg_match('/^http:\/\//', $location) || preg_match('/^https:\/\//', $location)){
			$this->location = $location;
		} else {
			$this->location = $this->http_redirect_base.$location.$param;
		}

		$this->location = str_ireplace('//', '/', $this->location);
		$this->location = str_ireplace('http:/', 'http://', $this->location);
	}

	public function setMessageID($id){
		try {
			StrictTypes::isInteger($id);
		} catch (BaseException $e){
			echo $e;
		}
		$this->message_id = $id;
	}
	public function setForceSSL(){
		if(!isset($_SERVER['HTTPS'])){
			$this->setLocation(str_replace('http://', 'https://', BASE_URL).$_SERVER['REQUEST_URI']);
		}
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
	public function getMessageID(){
		return $this->message_id;
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
	public function getHTTPRedirectBase(){
		return $this->http_redirect_base;
	}

	public function getHTTPReferer(){
		return $this->http_referer;
	}

	public function getStatusMessage(){
		$session = SessionHandler::getInstance();
		if($session->check(self::MSGID)){
			$DOMMessages = new DOMDocument('1.0','UTF-8');
			$DOMMessages->load(HTTP_STATUS_MESSAGE_FILE);
			$XPath = new DOMXPath($DOMMessages);
			$DOMMessage = $XPath->query('/messages/message[@id = '.$session->get(self::MSGID).']');
			try {
				if($DOMMessage->length > 1){
					throw new BaseException('Message Collission for messsage('.$session->get(self::MSGID).') ,in message file '.HTTP_STATUS_MESSAGE_FILE);
				} else if ($DOMMessage->length < 1) {
					// throw new BaseException('Non-excisting message('.$session->get(self::MSGID).'), in message file '.HTTP_STATUS_MESSAGE_FILE);
				} else {
					$session->remove(self::MSGID);
					return $DOMMessage->item(0);
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
		}
		return false;
	}
}

class PageFactoryPostTemplate extends PageFactoryWebAbstractTemplate {
	const TEMPLATE_ENGINE = 'PageFactoryPost';

	public function getSupportedTemplateEngineName(){
		return self::TEMPLATE_ENGINE;
	}
}
class PageFactoryPost extends PageFactoryTemplateEngine {
	public function draw(){
		$this->page->draw($this);
		return '';
	 }
	public function getSupportedTemplateDefinition(){ return __CLASS__; }
	public function addPageContent(Output $content){ return true; }
	public function addPageSettings(Output $settings){ return true; }
}
?>
