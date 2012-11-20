<?php
namespace Corelib\Base\PageFactory;

class Page {

	private $template = null;

	private $content = array();
	private $settings = array();

	/**
	 * @todo reimpliment Eventhandler when rewritten/moved
	 */
	public function prepare(){

		if($this->template->prepare()){

			\EventHandler::getInstance()->trigger(new Events\ApplySettings($this));

			while(list(,$val) = each($this->content)){
				$this->template->addContent($val);
			}
			while(list(,$val) = each($this->settings)){
				$this->template->addSettings($val);
			}

			return true;
		}
		return false;
	}

	public function setTemplate(Template $template){
		return $this->template = $template;
	}

	/**
	 * @param Template $template
	 *
	 * @return Template
	 * @deprecated
	 */
	public function addTemplateDefinition(Template $template){
		return $this->setTemplate($template);
	}

	/**
	 * Add Content to page.
	 *
	 * @param Output $content
	 * @param integer $cache cache type
	 * @param integer $ttl time to live in seconds
	 * @return Output
	 * @api
	 */
	public function addContent($content){
		if($content instanceof Output || $content instanceof \Output){
			return $this->content[] = $content;
		} else {
			throw new \Exception('Content is not of instance '.__NAMESPACE__.'\Output or \Output');
		}
		// assert('$content instanceof Output || $content instanceof \Output');

	}

	/**
	 * Add setting to page.
	 *
	 * @param Output $content
	 * @param integer $cache cache type
	 * @param integer $ttl time to live in seconds
	 * @return Output
	 * @api
	 */
	public function addSettings($settings){
		if($settings instanceof Output || $settings instanceof \Output){
			return $this->settings[] = $settings;
		} else {
			throw new \Exception('Content is not of instance '.__NAMESPACE__.'\Output or \Output');
		}
	}

	public function render(){

/*
		if(!is_null($this->converter)){
			return  $this->converter->convert($page);
		} else {
			return $page;
		}
*/
		return $this->template->render();
		/*
		if($engine->setTemplate($this->_getTemplateDefinition($engine))){
			EventHandler::getInstance()->trigger(new EventApplyDefaultSettings($this));

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
		*/
	}
}