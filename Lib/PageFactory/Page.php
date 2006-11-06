<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Abstract Page Class
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the 
 *	Bravura Distribution license that is available through the 
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura License and are
 *	unable to obtain it through the web, please send a note to 
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 * 
 *	@author Steffen Sørensen <steffen@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 *	@version 1.0.0 ($Id$)
 */

abstract class Page {
	private $settings = array();
	private $content = array();
	private $templates = array();
	
	abstract public function build();
	
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
	
	public function getSettings(){
		return $this->settings;
	}
	public function getContent(){
		return $this->settings;
	}
	
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
	
	public function __call($function, $args){
		$this->build();
	}
}

class EventApplyDefaultSettings implements Event {
	private $page = null;

	public function __construct(Page $page){
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