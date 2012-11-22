<?php
namespace Corelib\Base\Event;
use Corelib\Base\ServiceLocator\Service, Corelib\Base\ServiceLocator\Autoloadable;
use Corelib\Base\ServiceLocator\Locator, Corelib\Base\Log\Logger;

/**
 * Event handler class.
 *
 * @category corelib
 * @package Base
 * @subpackage EventHandler
 */
class Handler implements Service,Autoloadable {


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
	public function __construct(){
		// if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL && (!defined('PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR') || PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR)){
		//if(\Corelib\Base\ServiceLocator\Locator::isLoaded('Corelib\Base\PageFactory\Toolbar\Toolbar')){
		// $this->log = Locator::get('Corelib\Base\PageFactory\Toolbar\Toolbar', true)->addItem(new EventHandlerLogToolbar());
		//}

		// }
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
	public function register(Action $action, $event /*, [$event...] */){
		$events = func_get_args();
		array_shift($events);
		assert('sizeof($events) > 0');
		$action->register($this);

		foreach ($events as $event){
			if($action instanceof InstanceAction){
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
		$message = get_class($event) . ' triggered. Executed EventActions: ';
		foreach($actions AS $action) {
			$message .= get_class($action) . ' ';
		}
		Logger::info($message, 1);
	}
}

?>