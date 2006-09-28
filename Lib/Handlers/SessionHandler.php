<?php
/*	@version 1.0 ($Id$) */
if(!defined('SESSION_HANDLER_ENGINE')){
	/*
	*	Setup Session Handler Engine (developer only)
	*/
	define('SESSION_HANDLER_ENGINE', 'PHPSessionHandler');
}
class SessionHandler implements Singleton,Output {
	private static $instance = null;
	private static $init_by_event = false;
	private $engine = null;
	private $domain = '';
	private $lifetime = 0;
	private $path = '/';
	private $secure = false;


	public static function setInitByEvent($init=false){
		return self::$init_by_event = $init;
	}
	public static function getInitByEvent(){
		return self::$init_by_event;
	}

	private function __construct(){
		if(!self::getInitByEvent()){
			$this->initSession();
		}
	}
	public function initSession(){
		try {
			if(!defined('SESSION_HANDLER_ENGINE')){
				throw new BaseException('Session Handler Engine not defined');
			} else {
				$engine = '$this->engine = '.SESSION_HANDLER_ENGINE.'::getInstance();';
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
		return $xml->createElement('session_id', $this->getId());
	}
	public function &getArray(){
		return array('session_id', $this->getId());
	}
	public function getString($format = '%1$s'){
		return sprintf($format, $this->getId());
	}
	public function setDomain($domain){
		$this->domain = $domain;
	}

}

interface SessionHandlerEngine {
	public function set($name, $content);
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

class PHPSessionHandler implements SessionHandlerEngine,Singleton {
	private static $instance = null;
	private $domain = '';
	private $lifetime = 0;
	private $path = '/';
	private $secure = false;

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
	public function get($name){
		return $_SESSION[$name];
	}
	public function check($name){
		return isset($_SESSION[$name]);
	}
	public function remove($name){
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
}

class SessionHandlerInitEvent implements EventTypeHandler,Observer  {
	private $subject = null;

	public function __construct(){

	}
	public function getEventType(){
		return 'EventSessionConfigured';
	}
	public function register(ObserverSubject &$subject){
		$this->subject = $subject;
	}
	public function update($update){
		if(SessionHandler::getInitByEvent()){

			$session = SessionHandler::getInstance();
			$session->initSession();
		}
	}
}

class EventSessionConfigured implements Event {
		
}

$event = EventHandler::getInstance();
$event->registerObserver(new SessionHandlerInitEvent());
?>