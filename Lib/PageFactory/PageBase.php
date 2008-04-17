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

define('PAGE_OUTPUT_CACHE_DYNAMIC', 0);
define('PAGE_OUTPUT_CACHE_EXPIRE', 1);
define('PAGE_OUTPUT_CACHE_STATIC', 2);

abstract class PageBase {
	private $settings = array();
	private $content = array();
	private $templates = array();

	private $args = array();
	private $function = 'build';

	abstract public function build();

	public function addContent(Output $content, $cache=PAGE_OUTPUT_CACHE_DYNAMIC, $expire=false){
		$this->content[] = $content;
	}
	public function addSettings(Output $settings, $cache=PAGE_OUTPUT_CACHE_DYNAMIC, $expire=false){
		$this->settings[] = $settings;
	}
	/**
	 * @param PageFactoryTemplate $template
	 * @return PageFactoryTemplate
	 */
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

	protected function getArguments(){
		return $this->args;
	}
	protected function getFunction(){
		return $this->function;
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
	/*
	public function __call($function, $args){
		$this->args = &$args;
		$this->function = $function;
		$this->build();
	}
	*/
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
}

class EventApplyDefaultSettings implements Event {
	private $page = null;

	public function __construct(PageBase $page){
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