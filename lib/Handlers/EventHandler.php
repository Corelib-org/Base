<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	Event Handler
 *
 *	<i>No Description</i>
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
 * @subpackage EventHandler
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 2.0.0 ($Id$)
 */

//*****************************************************************//
//********************** EventHandler class ***********************//
//*****************************************************************//
/**
 * Event handler class.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 */
class EventHandler implements Singleton {


	//*****************************************************************//
	//**************** EventHandler class properties ******************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var EventHandler
	 * @internal
	 */
	private static $instance = null;

	/**
	 * @var EventHandlerLogToolbar
	 * @internal
	 * @since Version 5.0
	 */
	private $log = null;

	/**
	 * @var array event actions
	 * @internal
	 * @since Version 5.0
	 */
	private $actions = array();

	/**
	 * @var array instance event actions
	 * @internal
	 * @since Version 5.0
	 */
	private $instance_actions = null;


	//*****************************************************************//
	//****************** EventHandler class methods *******************//
	//*****************************************************************//
	/**
	 * Session handler constructor.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL && PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR){
			$this->log = PageFactoryDeveloperToolbar::getInstance()->addItem(new EventHandlerLogToolbar());
		}
	}

	/**
	 * 	Return instance of EventHandler.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses EventHandler::$instance
	 *	@return EventHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new EventHandler();
		}
		return self::$instance;
	}

	/**
	 * Register new event action.
	 *
	 * This method allows you to register a new event action
	 * on a number of events. this means a number of event names
	 * can be passed at parameter to this method. if EventAction is
	 * instance of {@link EventInstanceAction} it will be treated as
	 * a instance action. This method replaces the old registerObserver
	 * method.
	 *
	 * @param EventAction $action
	 * @param string $event events
	 * @return boolean true on success, else return false
	 * @since Version 5.0
	 */
	public function register(EventAction $action, $event /*, [$event...] */){
		$events = func_get_args();
		array_shift($events);
		assert('sizeof($events) > 0');
		$action->register($this);

		foreach ($events as $event){
			if($action instanceof EventInstanceAction){
				$this->instance_actions[$event][get_class($action)] = $action;
			} else {
				$this->actions[$event][get_class($action)] = $action;
			}
		}
		return true;
	}

	/**
	 * Trigger event.
	 *
	 * This methods allows the developer to trigger a event.
	 * This method replaces the old triggerEvent method.
	 *
	 * @param Event $event
	 * @return Event
	 * @since Version 5.0
	 */
	public function trigger(Event $event){
		$log = array();

		if(isset($this->actions[get_class($event)])){
			foreach ($this->actions[get_class($event)] as $action){
				$action->update($event);
				$log[] = $action;
			}
		}

		if(is_array($this->instance_actions)){
			foreach ($this->instance_actions as $instance => $actions){
				if($event instanceof $instance){
					foreach ($actions as $action){
						$log[] = $action;
						$action->update($event);
					}
				}
			}
		}
		$this->_log($event, $log);
		return $event;
	}

	/**
	 * Notify log toolbar item.
	 *
	 * @param Event $event
	 * @param array $actions
	 * @return void
	 * @internal
	 * @since 5.0
	 */
	private function _log(Event $event, array $actions){
		if(!is_null($this->log)){
			$this->log->add($event, $actions, debug_backtrace());
		}
		
		$message = get_class($event) . ' triggered. Executed EventActions: ';
		foreach($actions AS $action) {
			$message .= get_class($action) . ' ';
		}
		
		Logger::info($message);
	}
}


//*****************************************************************//
//************************ Event interface ************************//
//*****************************************************************//
/**
 * Event interface.
 *
 * This interfaces has no real meaning, it's used to
 * tag a class as a event and is used by the {@link EventHandler}.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 */
interface Event { }


//*****************************************************************//
//********************** EventAction class ************************//
//*****************************************************************//
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
abstract class EventAction {


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
	final public function register(EventHandler $eventhandler){
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

//*****************************************************************//
//***************** EventInstanceAction class *********************//
//*****************************************************************//
/**
 * Event instance action.
 *
 * This class should be extended in order to implement new event instance actions.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 * @since Version 5.0
 */
abstract class EventInstanceAction extends EventAction { }


//*****************************************************************//
//****************** EventRequestStart class **********************//
//*****************************************************************//
/**
 * Request start event.
 *
 * This basic event is triggered when a request starts.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 */
class EventRequestStart implements Event { }


//*****************************************************************//
//******************** EventRequestEnd class **********************//
//*****************************************************************//
/**
 * Request end event.
 *
 * This basic event is triggered when a request ends.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 */
class EventRequestEnd implements Event { }


//*****************************************************************//
//************** EventHandlerLogToolbar class *********************//
//*****************************************************************//
/**
 * Parsetime calculator toolbox item.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 *
 * @internal
 */
class EventHandlerLogToolbar extends PageFactoryDeveloperToolbarItem {


	//*****************************************************************//
	//********** EventHandlerLogToolbar class properties **************//
	//*****************************************************************//
	/**
	 * @var array event log
	 */
	private $log = null;


	//*****************************************************************//
	//*********** EventHandlerLogToolbar class methods ****************//
	//*****************************************************************//
	/**
	 * Add event information to log.
	 *
	 * @param Event $event
	 * @param array $actions
	 * @param array $backtrace
	 * @return void
	 */
	public function add(Event $event, array $actions, array $backtrace){
		$this->log[] = array('event' => $event, 'actions' => $actions, 'backtrace' => $backtrace);
	}

	/**
	 * Get toolbar item.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 */
	public function getToolbarItem(){
		return '<img src="corelib/resource/manager/images/icons/toolbar/events.png" alt="events" title="Events"/> '.(count($this->log));
	}

	/**
	 * Get toolbar content.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getContent()
	 * @uses ErrorHandler::getTraceAsHTML
	 */
	public function getContent(){
		if(count($this->log) > 0){
			$return = '';
			foreach($this->log as $log){
				$return .= '<h1>'.get_class($log['event']).'</h1>';
				$actions = array();
				foreach($log['actions'] as $action){
					$actions[] = '<b>'.get_class($action).'</b>';
				}
				$return .= implode(', ', $actions);
				$return .= '<div>'.ErrorHandler::getInstance()->getTraceAsHTML($log['backtrace']).'</div><br/>';
			}
			return $return;
		} else {
			return false;
		}
	}
}
?>