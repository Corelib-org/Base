<?php
namespace Corelib\Base\Session;
use Corelib\Base\PageFactory\Output;

/**
 * Session handler engine interface.
 *
 * A session engine must implement this interface.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
abstract class Engine {


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
	abstract public function set($name, $content);

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	abstract public function get($name);


	/**
	 * Get all session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	abstract public function &getVariables();

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	abstract public function check($name);

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	abstract public function remove($name);

	/**
	 * Lock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	abstract public function lock($name);

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	abstract public function isLocked($name);

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	abstract public function unlock($name);

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	abstract public function destroy();

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	abstract public function getID();

	/**
	 * Regenerate session ID.
	 *
	 * @return string
	 */
	abstract public function regenerateID();

	/**
	 * Initiate session.
	 *
	 * @param string $domain
	 * @param integer $lifetime
	 * @param string $path
	 * @param boolean $secure
	 * @return boolean true on success, else return false
	 */
	abstract public function init($ttl, $path, $domain, $secure, $name);
}