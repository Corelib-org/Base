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

class ManagerDashboard implements Output {
	/**
	 * @var ManagerConfig
	 */
	private $config = null;
	/**
	 * @var PageFactoryDOMXSLTemplate
	 */
	private $template = null;
	
	public function __construct(PageFactoryDOMXSLTemplate $template){
		$this->config = ManagerConfig::getInstance();
		$this->template = $template; 
	}
	
	public function getXML(DOMDocument $xml){
		$widgets = $xml->createElement('dashboard');
		
		$dashboard = $this->config->getPropertyXML('dashboard');
		for ($i = 0; $item = $dashboard->childNodes->item($i); $i++){
			if($item->nodeName == 'widget'){
				eval('$widget = new '.$item->getAttribute('handler').'();');
				$widget->setTemplate($this->template);
				$widget->setSettings($item); 
				$widgets->appendChild($widget->getXML($xml));
			}
		}
		return $widgets;
	}
	
	public function &getArray(){
		
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
		$update->getPage()->addSettings($this->config->getPropertyOutput('menu'));
	}
}
?>