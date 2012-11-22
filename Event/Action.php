<?php
namespace Corelib\Base\Event;


/**
 * Event action.
 *
 * This class should be extended in order to impliment new event actions.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 * @since Version 5.0
 */
abstract class Action {


	//*****************************************************************//
	//**************** EventAction class properties *******************//
	//*****************************************************************//
	/**
	 * @var EventHandler
	 * @internal
	 */
	private $eventhandler = null;


	//*****************************************************************//
	//****************** EventAction class methods ********************//
	//*****************************************************************//
	/**
	 * @param EventHandler $eventhandler
	 * @return boolean true on success, else return false
	 * @internal
	 */
	final public function register(Handler $eventhandler){
		$this->eventhandler = $eventhandler;
		return true;
	}


	//*****************************************************************//
	//************* EventAction class abstract methods ****************//
	//*****************************************************************//
	/**
	 * Take action on event.
	 *
	 * Impliment this method in order to action on a event.
	 *
	 * @param Event $event
	 * @return boolean true on success, else return false
	 */
	abstract public function update(Event $event);
}