<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Session Handler Base Classes
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
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


//*****************************************************************//
//********************* SessionHandler class **********************//
//*****************************************************************//
/**
 * Session handler.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
class SessionHandler implements Singleton,Output {


	//*****************************************************************//
	//*************** SessionHandler class properties *****************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var SessionHandler
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Engine reference.
	 *
	 * @var SessionHandlerEngine
	 * @internal
	 */
	private $engine = null;

	/**
	 * Cookie domain.
	 *
	 * @var string
	 * @internal
	 */
	private $domain = null;

	/**
	 * Cookie lifetime.
	 *
	 * @var integer seconds
	 * @internal
	 */
	private $lifetime = 0;

	/**
	 * Cookie path.
	 *
	 * @var string
	 * @internal
	 */
	private $path = '/';

	/**
	 * Secure cookie.
	 *
	 * @var boolean
	 * @internal
	 */
	private $secure = false;


	//*****************************************************************//
	//***************** SessionHandler class methods ******************//
	//*****************************************************************//
	/**
	 * Session handler constructor.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){
		if(!defined('SESSION_INIT_METHOD')){
			/**
			 * Define SessionHandler Init Method.
			 *
			 * The session handler can be initiated on to different ways.
			 * see the description for each constant.
			 *
			 * @uses SESSION_INIT_BY_EVENT
			 * @uses SESSION_INIT_BY_GET_INSTANCE
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
			$this->init();
		}
	}

	/**
	 * 	Return instance of SessionHandler.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses SessionHandler::$instance
	 *	@return SessionHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new SessionHandler();
		}
		return self::$instance;
	}

	/**
	 * Initiate session.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function init(){
		$engine = '$this->engine = '.SESSION_ENGINE.'::getInstance();';
		eval($engine);
		assert('$this->engine instanceof SessionHandlerEngine');
		assert('$this->engine instanceof Output');
		return $this->engine->init($this->lifetime, $this->path, $this->domain, $this->secure);
	}

	/**
	 * Set session variable.
	 *
	 * @param string $name
	 * @param mixed $content
	 * @return void
	 */
	public function set($name, $content){
		assert('is_string($name)');
		while($this->isLocked($name)){
			usleep(1);
		}
		$this->lock($name);
		$data = $this->engine->set($name, $content);
		$this->unlock($name);
	}

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name){
		assert('is_string($name)');
		return $this->engine->get($name);
	}

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	public function check($name){
		assert('is_string($name)');
		return $this->engine->check($name);
	}

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function remove($name){
		assert('is_string($name)');
		while($this->is_locked($name)){
			usleep(1);
		}
		$this->lock($name);
		$status = $this->engine->remove($name);
		return $status;
	}

	/**
	 * Lock session variable.
	 *
	 * This method does not work with all session handling
	 * engines. please refer to you engine for information
	 * about support.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function lock($name){
		assert('is_string($name)');
		while($this->isLocked($name)){
			usleep(1);
		}
		return $this->engine->lock($name);
	}

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	public function isLocked($name){
		assert('is_string($name)');
		return $this->engine->isLocked($name);
	}

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function unlock($name){
		return $this->engine->unlock($name);
	}

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	public function destroy(){
		return $this->engine->destroy();
	}

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function getID(){
		return $this->engine->getID();
	}

	/**
	 * Set output converter for session variable.
	 *
	 * @param string $name
	 * @param Converter $converter
	 * @return boolean true in success, else return false
	 */
	public function setConverter($name, Converter $converter){
		assert('is_string($name)');
		return $this->engine->setConverter($name, $converter);
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$session = $xml->createElement('session');
		$session->appendChild($xml->createElement('session_id', $this->getID()));
		$session->appendChild($this->engine->getXML($xml));
		return $session;
	}
}

//*****************************************************************//
//*************** SessionHandlerEngine interface ******************//
//*****************************************************************//
/**
 * Session handler engine interface.
 *
 * A session engine must implement this interface.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
interface SessionHandlerEngine {


	//*****************************************************************//
	//*********** SessionHandlerEngine interface methods **************//
	//*****************************************************************//
	/**
	 * Set session variable.
	 *
	 * @param string $name
	 * @param mixed $content
	 * @return boolean on success, else return false
	 */
	public function set($name, $content);

	/**
	 * Set output converter for session variable.
	 *
	 * @param string $name
	 * @param Converter $converter
	 * @return boolean true in success, else return false
	 */
	public function setConverter($name, Converter $converter);

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name);

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	public function check($name);

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function remove($name);

	/**
	 * Lock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function lock($name);

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	public function isLocked($name);

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function unlock($name);

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	public function destroy();

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function getID();

	/**
	 * Initiate session.
	 *
	 * @param string $domain
	 * @param integer $lifetime
	 * @param string $path
	 * @param boolean $secure
	 * @return boolean true on success, else return false
	 */
	public function init($lifetime, $path, $domain, $secure);
}


//*****************************************************************//
//******************* PHPSessionHandler class *********************//
//*****************************************************************//
/**
 * Session handler php engine.
 *
 * This engines uses the default PHP session handling functions.
 * and this engine is the default session handling engine.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
class PHPSessionHandler implements SessionHandlerEngine,Singleton,Output {


	//*****************************************************************//
	//************ PHPSessionHandler class properties *****************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var PHPSessionHandler
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Cookie domain.
	 *
	 * @var string
	 * @internal
	 */
	private $domain = '';

	/**
	 * Cookie lifetime.
	 *
	 * @var integer seconds
	 * @internal
	 */
	private $lifetime = 0;

	/**
	 * Cookie path.
	 *
	 * @var string
	 * @internal
	 */
	private $path = '/';

	/**
	 * Secure cookie.
	 *
	 * @var boolean
	 * @internal
	 */
	private $secure = false;

	/**
	 * List of output converters
	 *
	 * @var array
	 * @internal
	 */
	private $converters = array();


	//*****************************************************************//
	//************** PHPSessionHandler class methods ******************//
	//*****************************************************************//
	/**
	 * PHPSessionHandler constructor.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){ }

	/**
	 * 	Return instance of PHPSessionHandler.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses PHPSessionHandler::$instance
	 *	@return PHPSessionHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PHPSessionHandler();
		}
		return self::$instance;
	}

	/**
	 * Initiate session.
	 *
	 * @param string $domain
	 * @param integer $lifetime
	 * @param string $path
	 * @param boolean $secure
	 * @return boolean true on success, else return false
	 */
	public function init($lifetime, $path, $domain, $secure){
		$this->lifetime = $lifetime;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		ini_set('session.name', 'SSID');
		if(php_sapi_name() != 'cli'){
			session_start();
		}
	}

	/**
	 * Set session variable.
	 *
	 * @param string $name
	 * @param mixed $content
	 * @return boolean on success, else return false
	 */
	public function set($name, $content){
		$_SESSION[$name] = $content;
	}

	/**
	 * Set output converter for session variable.
	 *
	 * @param string $name
	 * @param Converter $converter
	 * @return boolean true in success, else return false
	 */
	public function setConverter($name, Converter $converter){
		$this->converters[$name] = $converter;
	}

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name){
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		} else {
			return false;
		}
	}

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	public function check($name){
		return isset($_SESSION[$name]);
	}

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function remove($name){
		session_unregister($name);
		unset($_SESSION[$name]);
	}

	/**
	 * Lock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function lock($name){
		return true;
	}

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	public function isLocked($name){
		return false;
	}

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function unlock($name){
		return true;
	}

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	public function destroy(){
		setcookie(session_name(), '', time()-42000, $this->path);
		session_destroy();
	}

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function getID(){
		return session_id();
	}

	/**
	 * Set session cookie parameters.
	 *
	 * @return void
	 * @internal
	 */
	private function _setCookieParams(){
		session_set_cookie_params($this->lifetime, $this->path, $this->domain, $this->secure);
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$session = $xml->createElement('session');
		$this->_getXMLArray($session, $_SESSION);
		return $session;
	}

	/**
	 * Convert session array to dom tree.
	 *
	 * @param DOMElement $parent
	 * @param array $array
	 * @return void
	 * @internal
	 */
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


//*****************************************************************//
//**************** SessionHandlerInitEvent class ******************//
//*****************************************************************//
/**
 * Session handler init event.
 *
 * This event init's the session if init session by event
 * have been selected.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
class SessionHandlerInitEvent extends EventAction  {


	//*****************************************************************//
	//************ SessionHandlerInitEvent class methods **************//
	/**
	 * Update with event.
	 *
	 * @see Observer::update()
	 */
	public function update(Event $event){
		$session = SessionHandler::getInstance();
		if(SESSION_INIT_METHOD == SESSION_INIT_BY_EVENT){
			$session->init();
		}
	}
}

//*****************************************************************//
//**************** SessionHandlerInitEvent class ******************//
//*****************************************************************//
/**
 * Session init event.
 *
 * Trigger this event to init the session handler.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
class EventSessionConfigured implements Event {

}

$event = EventHandler::getInstance();
$event->register(new SessionHandlerInitEvent(), 'EventSessionConfigured');
?>