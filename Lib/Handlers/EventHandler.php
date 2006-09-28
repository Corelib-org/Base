<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	Event Handler
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the 
 *	Morkland Distribution license that is available through the 
 *	world-wide-web at the following URI: http://www.morkland.com/license/dist_1.0.txt.
 *	If you did not receive a copy of the PHP License and are
 *	unable to obtain it through the web, please send a note to 
 *	license@morkland.com so we can mail you a copy immediately.
 *
 *	@author Steffen S&Oslash;rensen <steffen@morkland.com>
 *	@copyright Copyright &copy; 2005, Morkland
 * 	@license http://www.morkland.com/licence/dist_1.0.txt
 *	@package corelib
 *	@subpackage EventHandler
 *	@link http://www.morkland.com
 *	@version 1.0 Beta ($Id$)
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
	
	public function registerObserver(Observer &$observer){
		// Lets find out about exceptions and but one here, to check for the right type of observer is given
		try {
			if(!$observer instanceof EventTypeHandler){
				throw new BaseException('Invalid InputTypeHandler given');	
			}
		} catch (Exception $e){
			echo $e;
		}
		
		$this->handlers[$observer->getEventType()][get_class($observer)] = &$observer;
	}
	
	public function removeObserver(Observer &$observer){
		
	}
	
	public function notifyObservers(){
		
	}
	
	public function triggerEvent(Event &$event){
		if(isset($this->handlers[get_class($event)])){
			while(list(,$val) = each($this->handlers[get_class($event)])){
				$val->update($event);
			}
		    reset($this->handlers[get_class($event)]);
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
