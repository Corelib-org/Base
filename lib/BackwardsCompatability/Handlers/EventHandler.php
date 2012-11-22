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
// use Corelib\Base\ErrorHandler;

//*****************************************************************//
//********************** EventHandler class ***********************//
//*****************************************************************//



//*****************************************************************//
//************************ Event interface ************************//
//*****************************************************************//


//*****************************************************************//
//********************** EventAction class ************************//
//*****************************************************************//


//*****************************************************************//
//***************** EventInstanceAction class *********************//
//*****************************************************************//


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
// class EventRequestStart implements Event { }


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
// class EventRequestEnd implements Event { }


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
/*
class EventHandlerLogToolbar extends \Corelib\Base\PageFactory\Toolbar\Item {

*/
	//*****************************************************************//
	//********** EventHandlerLogToolbar class properties **************//
	//*****************************************************************//
	/**
	 * @var array event log
	 */
//	private $log = null;


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
//	public function add(Event $event, array $actions, array $backtrace){
//		$this->log[] = array('event' => $event, 'actions' => $actions, 'backtrace' => $backtrace);
//	}

	/**
	 * Get toolbar item.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 */
//	public function getToolbarItem(){
//		return '<img src="corelib/resource/manager/images/icons/toolbar/events.png" alt="events" title="Events"/> '.(count($this->log));
//	}

	/**
	 * Get toolbar content.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getContent()
	 * @uses ErrorHandler::getTraceAsHTML
	 */
/*
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
				// $return .= '<div>'.ErrorHandler::getInstance()->getTraceAsHTML($log['backtrace']).'</div><br/>';
			}
			return $return;
		} else {
			return false;
		}
	}
}
*/
?>