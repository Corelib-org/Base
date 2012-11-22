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
// use Corelib\Base\Converters\Converter, Corelib\Base\PageFactory\Output;

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
//if(!defined('SESSION_ENGINE')){
	/**
	 * Setup Session Handler Engine (developer only).
	 *
	 * @var string session handler engine class name.
	 * @internal
	 */
//	define('SESSION_ENGINE', 'PHPSessionHandler');
//}

/**
 *	Init Session By Event
 *
 * 	@see SESSION_INIT_METHOD
 */
//define('SESSION_INIT_BY_EVENT', 1);

/**
 * 	Init Session By Get Instance
 *
 * 	@see SESSION_INIT_METHOD
 */
// define('SESSION_INIT_BY_GET_INSTANCE', 2);


//*****************************************************************//
//********************* SessionHandler class **********************//
//*****************************************************************//

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
// interface SessionHandlerEngine {


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
	// public function set($name, $content);

	/**
	 * Set output converter for session variable.
	 *
	 * @param string $name
	 * @param Converter $converter
	 * @return boolean true in success, else return false
	 */
	// public function setConverter($name, Converter $converter);

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	// public function get($name);

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	// public function check($name);

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	// public function remove($name);

	/**
	 * Lock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	// public function lock($name);

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	// public function isLocked($name);

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	// public function unlock($name);

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	// public function destroy();

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	// public function getID();

	/**
	 * Regenerate session ID.
	 *
	 * @return string
	 */
	// public function regenerateID();

	/**
	 * Initiate session.
	 *
	 * @param string $domain
	 * @param integer $lifetime
	 * @param string $path
	 * @param boolean $secure
	 * @return boolean true on success, else return false
	 */
	// public function init($lifetime, $path, $domain, $secure);
// }


//*****************************************************************//
//******************* PHPSessionHandler class *********************//
//*****************************************************************//


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

//class SessionHandlerInitEvent extends EventAction  {


	//*****************************************************************//
	//************ SessionHandlerInitEvent class methods **************//
	//*****************************************************************//
	/**
	 * Update with event.
	 *
	 * @see Observer::update()
	 */
/*
	public function update(Event $event){
		$session = Session::getInstance();
		if(SESSION_INIT_METHOD == SESSION_INIT_BY_EVENT){
			$session->init();
		}
	}
}
*/
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
/*
class EventSessionConfigured implements Event {

}

$event = EventHandler::getInstance();
$event->register(new SessionHandlerInitEvent(), 'EventSessionConfigured');


if(!class_exists('SessionHandler', false)){
	class SessionHandler implements Singleton {
		public static function getInstance(){
			return Session::getInstance();
		}
	}
}
*/
?>