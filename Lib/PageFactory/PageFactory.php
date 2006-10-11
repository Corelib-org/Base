<?php
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