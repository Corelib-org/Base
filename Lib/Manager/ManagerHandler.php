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
	
	public function getSystemCheckResults(){
		if($checks = $this->getPropertyXML('systemchecks')){
			$i = 0;
			while($check = $checks->childNodes->item($i++)){
				if($check instanceof DOMElement){
					switch($check->getAttribute('type')){
						case 'permission':
							$oi = 0;
							while($object = $check->childNodes->item($oi++)){
								$result = $object->appendChild($checks->ownerDocument->createElement('result'));
								switch ($object->nodeName){
									case 'folder':
										$folder = Manager::parseConstantTags($object->getAttribute('folder'));
										if(is_dir($folder)){
											$is_dir = 'true';
										} else {
											$is_dir = 'false';
										}														
										$result->appendChild($checks->ownerDocument->createElement('dir', $is_dir));
										if($object->getAttribute('readable') == 'true'){
											if(is_readable($folder)){
												$readable = 'true';
											} else {
												$readable = 'false';
											}
											$result->appendChild($checks->ownerDocument->createElement('readable', $readable));
										}
										if($object->getAttribute('writable') == 'true'){
											if(is_writable($folder)){
												$writable = 'true';
											} else {
												$writable = 'false';
											}
											
											$result->appendChild($checks->ownerDocument->createElement('writable', $writable));
										}
										break;
									case 'file':
										$file = Manager::parseConstantTags($object->getAttribute('file'));
										$object->setAttribute('file', $file);
										if(is_file($file)){
											$is_file = 'true';
										} else {
											$is_file = 'false';
										}											
									
										$result->appendChild($checks->ownerDocument->createElement('file', $is_file));
										if($object->getAttribute('readable') == 'true'){
											if(is_readable($file)){
												$readable = 'true';
											} else {
												$readable = 'false';
											}											
											$result->appendChild($checks->ownerDocument->createElement('readable', $readable));
										}
										if($object->getAttribute('writable') == 'true'){
											if(is_writable($file)){
												$writable = 'true';
											} else {
												$writable = 'false';
											}											
											$result->appendChild($checks->ownerDocument->createElement('writable', $writable));
										}
										break;
								}
							}
							break;
					}
				}
			}
		}
		return $this->getPropertyOutput('systemchecks');
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