<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	Session Handler Base Classes
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

if(!defined('SESSION_ENGINE')){
	/*
	*	Setup Session Handler Engine (developer only)
	*/
	define('SESSION_ENGINE', 'PHPSessionHandler');
}

/**
 *	Init Session By Event
 * 
 * 	@see SESSION_INIT_METHOD
 */
define('SESSION_INIT_BY_EVENT', 1);

/**
 * 	Init Session By Get Instance
 * 
 * 	@see SESSION_INIT_METHOD
 */
define('SESSION_INIT_BY_GET_INSTANCE', 2);

class SessionHandler implements Singleton,Output {
	private static $instance = null;
	/**
	 * @var SessionHandlerEngine
	 */
	private $engine = null;
	private $domain = '';
	private $lifetime = 0;
	private $path = '/';
	private $secure = false;

	private $converters = array();
	
	private function __construct(){
		if(!defined('SESSION_INIT_METHOD')){
			/**
			 * 	Define SessionHandler Init Method
			 * 
			 *	@uses SESSION_INIT_BY_EVENT
			 * 	@uses SESSION_INIT_BY_GET_INSTANCE
			 */
			define('SESSION_INIT_METHOD', SESSION_INIT_BY_GET_INSTANCE);
		}
		if(!defined('SESSION_DOMAIN')){
			/**
			 * Define Session Domain
			 */
			define('SESSION_DOMAIN', null);
		}
		$this->domain = SESSION_DOMAIN;
		if(!defined('SESSION_LIFETIME')){
			/**
			 * Define session lifetime
			 */
			define('SESSION_LIFETIME', 0);
		}
		$this->lifetime = SESSION_LIFETIME;
		if(!defined('SESSION_PATH')){
			/**
			 * Define Session Path
			 */
			define('SESSION_PATH', '/');
		}
		$this->path = SESSION_PATH;
		if(!defined('SESSION_SECURE')){
			/**
			 * Define session secure
			 */
			define('SESSION_SECURE', false);
		}
		$this->secure = SESSION_SECURE;
		
		if(SESSION_INIT_METHOD == SESSION_INIT_BY_GET_INSTANCE){
			$this->initSession();
		}
	}
	
	public function initSession(){
		try {
			if(!defined('SESSION_ENGINE')){
				throw new BaseException('Session Handler Engine not defined');
			} else {
				$engine = '$this->engine = '.SESSION_ENGINE.'::getInstance();';
				eval($engine);
				if(!$this->engine instanceof SessionHandlerEngine){
					throw new BaseException('Invalid Session Engine Given, Session handler engine must be a instance of SessionHandlerType.');
				} else {
					$this->engine->setDomain($this->domain);
					$this->engine->init();
				}
				
			}
		} catch (Exception $e){
			echo $e;
		}
	}
	public function set($name, $content){
		while($this->is_locked($name)){
			usleep(1);
		}
		$this->lock($name);
		$data = $this->engine->set($name, $content);
		$this->unlock($name);
	}
	public function get($name){
		return $this->engine->get($name);
	}
	public function check($name){
		return $this->engine->check($name);
	}
	public function remove($name){
		while($this->is_locked($name)){
			usleep(1);
		}
		$this->lock($name);
		$status = $this->engine->remove($name);
		return $status;
	}
	public function lock($name){
		while($this->is_locked($name)){
			usleep(1);
		}
		return $this->engine->lock($name);
	}
	public function is_locked($name){
		return $this->engine->is_locked($name);
	}
	public function unlock($name){
		$this->engine->unlock($name);
	}
	public function destroy(){
		return $this->engine->destroy();
	}
	public function getId(){
		return $this->engine->getId();
	}

	public function setConverter($name, Converter $converter){
		$this->engine->setConverter($name, $converter);
	}

	/**
	 *	@return SessionHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new SessionHandler();
		}
		return self::$instance;
	}
	public function getXML(DOMDocument $xml){
		$session = $xml->createElement('session');
		$session->appendChild($xml->createElement('session_id', $this->getId()));
		$session->appendChild($this->engine->getXML($xml));
		return $session;
	}
	public function &getArray(){
		return array('session_id', $this->getId());
	}
	public function getString($format = '%1$s'){
		return sprintf($format, $this->getId());
	}
}

interface SessionHandlerEngine {
	public function set($name, $content);
	public function setConverter($name, Converter $converter);
	public function get($name);
	public function check($name);
	public function remove($name);
	public function lock($name);
	public function is_locked($name);
	public function unlock($name);
	public function destroy();
	public function getId();
	public function init();
	public function setDomain($domain);
}

class PHPSessionHandler implements SessionHandlerEngine,Singleton,Output {
	private static $instance = null;
	private $domain = '';
	private $lifetime = 0;
	private $path = '/';
	private $secure = false;
	
	private $converters = array();

	private function __construct(){
	}

	public function init(){
		ini_set('session.name', 'SSID');
		if(php_sapi_name() != 'cli'){
			session_start();
		}
	}
	public function set($name, $content){
		$_SESSION[$name] = $content;
	}
	
	public function setConverter($name, Converter $converter){
		$this->converters[$name] = $converter;
	}	
	
	public function get($name){
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		} else {
			return false;
		}
	}
	public function check($name){
		return isset($_SESSION[$name]);
	}
	public function remove($name){
		session_unregister($name);
		unset($_SESSION[$name]);
	}
	public function lock($name){
		return true;
	}
	public function is_locked($name){
		return false;
	}
	public function unlock($name){
		return true;
	}
	public function destroy(){
		session_destroy();
	}
	public function getId(){
		return session_id();
	}
	public function setDomain($domain){
		$this->domain = $domain;
		$this->_setCookieParams();
	}
	private function _setCookieParams(){
		session_set_cookie_params($this->lifetime, $this->path, $this->domain, $this->secure);
	}
	/**
	 *	@return PHPSessionHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PHPSessionHandler();
		}
		return self::$instance;
	}
	
	public function getXML(DOMDocument $xml){
		$session = $xml->createElement('session');
		$this->_getXMLArray($session, $_SESSION);
		return $session;
	}
	public function &getArray(){
		return array('session_id', $this->getId());
	}

	
	private function _getXMLArray(DOMElement $parent, array $array){
		while(list($key, $val) = each($array)){
			if(preg_match('/(^[0-9])|(,)/', $key)){
				$key = 'item';
			}
			if(is_array($val)){
				$key = $parent->appendChild($parent->ownerDocument->createElement($key));
				$this->_getXMLArray($key, $val);
			} else {
				if(isset($this->converters[$key])){
					$val = $this->converters[$key]->convert($val);
				}
				$parent->appendChild($parent->ownerDocument->createElement($key, htmlspecialchars($val)));
			}
		}
	}
}

class SessionHandlerInitEvent implements EventTypeHandler,Observer  {
	private $subject = null;

	public function __construct(){

	}
	public function getEventType(){
		return 'EventSessionConfigured';
	}
	public function register(ObserverSubject $subject){
		$this->subject = $subject;
	}
	public function update($update){
		$session = SessionHandler::getInstance();
		if(SESSION_INIT_METHOD == SESSION_INIT_BY_EVENT){
			$session->initSession();
		}
	}
}

class EventSessionConfigured implements Event {
		
}

$event = EventHandler::getInstance();
$event->registerObserver(new SessionHandlerInitEvent());
?>