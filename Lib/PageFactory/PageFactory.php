<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Base Classes
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
 *	@author Steffen S&oslash;rensen <steffen@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 *	@version 1.0.0 ($Id: Base.php 2966 2006-10-11 09:30:36Z wayland $)
 */
if(!defined('REDIRECT_URL')){
	/**
	 * Superceeded by {@link HTTP_REDIRECT_BASE}
	 *
	 * @deprecated Superceeded by HTTP_REDIRECT_BASE
	 */
	define('REDIRECT_URL', 'http://'.$_SERVER['SERVER_NAME'].'/');	
} else {
	try {
		throw new BaseException('constant REDIRECT_URL is deprecated, it has been superceeded by HTTP_REDIRECT_BASE');
	} catch (BaseException $e){
		echo $e;
	}
	define('HTTP_REDIRECT_BASE', REDIRECT_URL);
}

abstract class PageFactoryTemplate {
	public function init(){
		return true;
	}
	
	abstract public function getSupportedTemplateEngineName();
	abstract public function cleanup();

}

abstract class PageFactoryTemplateEngine {
	/**
	 * @var Page
	 */
	protected $page = null;
	/**
	 * @var PageFactoryTemplate
	 */
	protected $template = null;
	
	public function build(Page $page){
		$this->page = $page;
		$this->page->build();
	}
	
	public function setTemplate(PageFactoryTemplate $template){
		$this->template = $template;
		return $this->template->init();
	}
	/**
	 * @return PageFactoryTemplate
	 */
	public function getTemplate(){
		return $this->template;
	}
	
	abstract public function draw();
	abstract public function getSupportedTemplateDefinition();
	abstract public function addPageContent(Output $content);
	abstract public function addPageSettings(Output $settings);
}


class PageFactory implements Singleton {
	/**
	 *	@var PageFactory
	 */
	private static $instance = null;
	/**
	 * @var PageFactoryTemplateEngine
	 */
	private $engine = null;


	private function __construct(){	
		$this->engine = new PageFactoryDOMXSL();
	}

	/**
	 *	@return PageFactory
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PageFactory();
		}
		return self::$instance;
	}	
	
	public function build(Page $page){
		$this->engine->build($page);
	}

	public function draw(){
		$this->engine->draw();
		$template = $this->engine->getTemplate();
		$template->cleanup();
	}
}
?>