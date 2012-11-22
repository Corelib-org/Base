<?php
namespace Corelib\Base\Session\Engines;
use Corelib\Base\Session\Engine;

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
class PHP extends Engine {


	//*****************************************************************//
	//************ PHPSessionHandler class properties *****************//
	//*****************************************************************//
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
	private $ttl = 0;

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
	//************** PHPSessionHandler class methods ******************//
	//*****************************************************************//
	/**
	 * Initiate session.
	 *
	 * @param string $domain
	 * @param integer $lifetime
	 * @param string $path
	 * @param boolean $secure
	 * @return boolean true on success, else return false
	 */
	public function init($ttl, $path, $domain, $secure, $name){
		$this->ttl = $ttl;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;

		if(php_sapi_name() != 'cli'){
			ini_set('session.name', $name);
			return session_start();
		} else {
			return false;
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
	 * Regenerate session ID.
	 *
	 * @return string
	 */
	public function regenerateID(){
		return session_regenerate_id(true);
	}


	/**
	 * Get all session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &getVariables(){
		return $_SESSION;
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


}

?>