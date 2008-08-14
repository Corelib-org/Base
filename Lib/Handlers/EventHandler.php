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
 * @author Steffen S&Oslash;rensen <steffen@morkland.com>
 * @copyright Copyright &copy; 2005, Morkland
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage EventHandler
 * @link http://www.corelib.org
 * @version 1.0.1 ($Id$)
 */

class EventHandler implements Singleton,ObserverSubject {
	private static $instance = null;
	
	private $handlers = array();
	
	
	private function __construct(){
		
	}
	/**
	 *	@return EventHandler
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new EventHandler();
		}
		return self::$instance;	
	}
	
	public function registerObserver(Observer $observer){
		try {
			if(!$observer instanceof EventTypeHandler){
				throw new BaseException('Invalid InputTypeHandler given');	
			}
		} catch (Exception $e){
			echo $e;
		}
		if(is_array($events = $observer->getEventType())){
			foreach ($events as $event){ 
				$this->handlers[$event][get_class($observer)] = $observer;	
			}
		} else {
			$this->handlers[$events][get_class($observer)] = $observer;
		}
	}
	
	public function removeObserver(Observer $observer){
		
	}
	
	public function notifyObservers(){
		
	}
	
	public function triggerEvent(Event $event){
		if(isset($this->handlers[get_class($event)])){
			foreach ($this->handlers[get_class($event)] as $val){
				$val->update($event);
			}
		}
		return $event;
	}
}

interface EventTypeHandler {
	public function getEventType();	
}

interface Event {
	
}

class EventRequestStart implements Event {
		
}
class EventRequestEnd implements Event {
		
}
?>
