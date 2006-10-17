<?php
abstract class Page {
	private $settings = array();
	private $content = array();
	private $templates = array();
	
	public function addContent(Output $content){
		$this->content[] = $content;
	}
	
	public function addSettings(Output $settings){
		$this->settings[] = $settings;
	}
	
	public function addTemplateDefinition(PageFactoryTemplate $template){
		$this->templates[$template->getSupportedTemplateEngineName()] = $template;
		
		return $template;
	}
	
	private function _getTemplateDefinition(PageFactoryTemplateEngine $engine){
		try {
			if(!isset($this->templates[$engine->getSupportedTemplateDefinition()])){
				throw new BaseException('Unable to find template for given template engine', E_USER_ERROR);
			} else {
				return $this->templates[$engine->getSupportedTemplateDefinition()];
			}
		} catch (BaseException $e){
			echo $e;
			exit;
		}
	}
	
	public function getSettings(){
		return $this->settings;
	}

	public function getContent(){
		return $this->settings;
	}
	
	abstract public function build();
	
	public function draw(PageFactoryTemplateEngine $engine){
		if($engine->setTemplate($this->_getTemplateDefinition($engine))){
			$event = EventHandler::getInstance();
			$event->triggerEvent(new EventApplyDefaultSettings($this));

			while(list(,$val) = each($this->content)){
				$engine->addPageContent($val);
			}
			while(list(,$val) = each($this->settings)){
				$engine->addPageSettings($val);
			}
			return true;
		} else {
			return false;
		}
	}
}

class EventApplyDefaultSettings implements Event {
	private $page = null;

	function __construct(Page $page){
		$this->page = $page;
	}
	/**
	 * @return Page
	 */
	public function getPage(){
		return $this->page;
	}
}
?>