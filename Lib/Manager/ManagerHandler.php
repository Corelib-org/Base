<?php
class ManagerConfig extends CorelibManagerExtension {
	private static $instance = null;
	/**
	 *	@return ManagerConfig
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new ManagerConfig();
		}
		return self::$instance;
	}
	
	public function __construct(){
		$event = EventHandler::getInstance();
		$event->registerObserver(new ManagerConfigAddSettings($this));
	}
	
	/**
	 * @return returns a valid output object containing the menu.
	 */
	public function getMenuOutput(){
		$output = new GenericOutput();
		$output->setXML($this->getPropertyXML('menu'));
		return $output;
	}
	
	public function getResourceDir($handle){
		if($resources = $this->getPropertyXML('resources')){
			$xpath = new DOMXPath($resources->ownerDocument);
			$xpath = $xpath->query('resource[@handle = \''.$handle.'\']', $resources);
			if($xpath->length > 0){
				return Manager::parseConstantTags($xpath->item(0)->nodeValue);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

class ManagerConfigAddSettings implements EventTypeHandler,Observer  {
	private $subject = null;
	/**
	 * @var ManagerConfig
	 */
	private $config = null;
	
	public function __construct(ManagerConfig $config){
		$this->config = $config;
	}
	
	public function getEventType(){
		return 'EventApplyDefaultSettings';	
	}	
	public function register(ObserverSubject $subject){
		$this->subject = $subject;
	}
	public function update($update){
		$update->getPage()->addSettings($this->config->getMenuOutput());
	}
}
?>