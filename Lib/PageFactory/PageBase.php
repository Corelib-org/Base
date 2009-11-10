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
define('PAGE_OUTPUT_CACHE_DISABLED', 0);
define('PAGE_OUTPUT_CACHE_DYNAMIC', 1);
define('PAGE_OUTPUT_CACHE_STATIC', 2);

abstract class PageBase {
	private $settings = array();
	private $content = array();
	private $templates = array();

	private $args = array();
	private $function = 'build';

	/**
	 * @var CacheManager
	 */
	private $cache = null;

	public final function __construct(){

	}

	public function __init(){ }

	abstract public function build();

	final public function setCacheManager(CacheManager $cache){
		$this->cache = $cache;
	}

	public function addContent(Output $content, $cache=PAGE_OUTPUT_CACHE_DISABLED, $ttl=false){
		if($cache == PAGE_OUTPUT_CACHE_DYNAMIC  && $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
			$this->cache->addDynamicContent($content);
		} else {
			$this->content[] = array('object' => $content, 'cache'=>$cache, 'ttl'=>$ttl);
		}
		return $content;
	}
	public function addSettings(Output $settings, $cache=PAGE_OUTPUT_CACHE_DISABLED, $ttl=false){
		if($cache == PAGE_OUTPUT_CACHE_DYNAMIC  && $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
			$this->cache->addDynamicSettings($settings);
		} else {
			$this->settings[] = array('object' => $settings, 'cache'=>$cache, 'ttl'=>$ttl);
		}
		return $settings;
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


	/**
	 * Draw all elements on page.
	 *
	 * @param PageFactoryTemplateEngine $engine
	 * @return boolean true on success else return false
	 */
	final public function draw(PageFactoryTemplateEngine $engine){

		if($engine->setTemplate($this->_getTemplateDefinition($engine))){
			EventHandler::getInstance()->triggerEvent(new EventApplyDefaultSettings($this));

			while(list(,$val) = each($this->content)){
				if(($val['cache'] == PAGE_OUTPUT_CACHE_STATIC && !$this->cache->isCached()) || !PAGE_FACTORY_CACHE_ENABLE){
					$this->_registerCacheInformation($val);
				}
				$engine->addPageContent($val['object']);
			}
			while(list(,$val) = each($this->settings)){
				if(($val['cache'] == PAGE_OUTPUT_CACHE_STATIC && !$this->cache->isCached()) || !PAGE_FACTORY_CACHE_ENABLE){
					$this->_registerCacheInformation($val);
				}
				$engine->addPageSettings($val['object']);
			}
			return true;
		} else {
			return false;
		}
		return true;
	}

	protected function isCached(){
		return $this->cache->isCached() && $this->cache->getType() != PAGE_FACTORY_CACHE_STATIC;
	}

	private function _registerCacheInformation(array $item){
		if($item['object'] instanceof CacheableOutput){
			$this->cache->getCacheManagerOutput($item['object'], $item['cache'], $item['ttl']);
		}
	}

	private function _getTemplateDefinition(PageFactoryTemplateEngine $engine){
		try {
			if(!isset($this->templates[$engine->getSupportedTemplateDefinition()])){
				throw new BaseException('Unable to find template for given template engine: '.$engine->getSupportedTemplateDefinition(), E_USER_ERROR);
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