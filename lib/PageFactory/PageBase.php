<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory Abstract Page Class
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.1.0 ($Id$)
 */

//*****************************************************************//
//*********************** Define Contants *************************//
//*****************************************************************//
/**
 * Page output disabled caching mode.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_OUTPUT_CACHE_DISABLED', 0);

/**
 * Page output dynamic caching mode.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_OUTPUT_CACHE_DYNAMIC', 1);

/**
 * Page output static caching mode.
 *
 * @since Version 5.0
 * @var integer
 */
define('PAGE_OUTPUT_CACHE_STATIC', 2);


//*****************************************************************//
//************************ PageBase class *************************//
//*****************************************************************//
/**
 * Page factory page base.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
abstract class PageBase {


	//*****************************************************************//
	//****************** PageBase class properties ********************//
	//*****************************************************************//
	/**
	 * List of settings output.
	 *
	 * @var array
	 * @internal
	 */
	private $settings = array();

	/**
	 * List of content output.
	 *
	 * @var array
	 * @internal
	 */
	private $content = array();

	/**
	 * List of template definitinos.
	 *
	 * @var array
	 * @internal
	 */
	private $templates = array();

	/**
	 * Cache manager reference.
	 *
	 * @var CacheManager
	 * @internal
	 */
	private $cache = null;


	//*****************************************************************//
	//******************* PageBase class methods **********************//
	//*****************************************************************//
	/**
	 * @ignore
	 */
	public final function __construct(){ }

	/**
	 * PageBase init.
	 *
	 * This method may be overwritten and should be threated as
	 * a object constructor.
	 *
	 * @return void
	 */
	public function __init(){ }

	/**
	 * Set cache manager.
	 *
	 * Set cache manager reference.
	 *
	 * @param CacheManager $cache
	 * @return void
	 * @internal
	 */
	final public function setCacheManager(CacheManager $cache){
		$this->cache = $cache;
	}

	/**
	 * Add Content to page.
	 *
	 * @param Output $content
	 * @param integer $cache cache type
	 * @param integer $ttl time to live in seconds
	 * @return Output
	 */
	public function addContent(Output $content, $cache=PAGE_OUTPUT_CACHE_DISABLED, $ttl=false){
		if($cache == PAGE_OUTPUT_CACHE_DYNAMIC  && $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
			$this->cache->addDynamicContent($content);
		} else {
			$this->content[] = array('object' => $content, 'cache'=>$cache, 'ttl'=>$ttl);
		}
		return $content;
	}

	/**
	 * Add setting to page.
	 *
	 * @param Output $content
	 * @param integer $cache cache type
	 * @param integer $ttl time to live in seconds
	 * @return Output
	 */
	public function addSettings(Output $settings, $cache=PAGE_OUTPUT_CACHE_DISABLED, $ttl=false){
		if($cache == PAGE_OUTPUT_CACHE_DYNAMIC  && $this->cache->getType() == PAGE_FACTORY_CACHE_DYNAMIC){
			$this->cache->addDynamicSettings($settings);
		} else {
			$this->settings[] = array('object' => $settings, 'cache'=>$cache, 'ttl'=>$ttl);
		}
		return $settings;
	}

	/**
	 * Add template definition.
	 *
	 * @param PageFactoryTemplate $template
	 * @return PageFactoryTemplate
	 */
	public function addTemplateDefinition(PageFactoryTemplate $template){
		$this->templates[$template->getSupportedTemplateEngineName()] = $template;
		return $template;
	}

	/**
	 * Draw all elements on page.
	 *
	 * @param PageFactoryTemplateEngine $engine
	 * @return boolean true on success else return false
	 */
	final public function draw(PageFactoryTemplateEngine $engine){
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
	}

	/**
	 * Check to see if page i cached.
	 *
	 * @return boolean true if cached, else return false
	 */
	protected function isCached(){
		return $this->cache->isCached() && ($this->cache->getType() != PAGE_FACTORY_CACHE_STATIC);
	}

	/**
	 * Register cache informations.
	 *
	 * @param array $item
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _registerCacheInformation(array $item){
		if($item['object'] instanceof CacheableOutput){
			$this->cache->getCacheManagerOutput($item['object'], $item['cache'], $item['ttl']);
		}
		return true;
	}

	/**
	 * Get template definition.
	 *
	 * @param PageFactoryTemplateEngine $engine
	 * @return PageFactoryTemplate on success, else return false
	 */
	private function _getTemplateDefinition(PageFactoryTemplateEngine $engine){
		if(!isset($this->templates[get_class($engine)])){
			trigger_error('Unable to find template for given template engine: '.get_class($engine), E_USER_ERROR);
			return false;
		} else {
			return $this->templates[get_class($engine)];
		}
	}
}


//*****************************************************************//
//************ EventApplyDefaultSettings event class **************//
//*****************************************************************//
/**
 * Apple default settings.
 *
 * This event is triggered when the page is drawn
 * making it possible to inject content into a page
 * automatically.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class EventApplyDefaultSettings implements Event {


	//*****************************************************************//
	//******* EventApplyDefaultSettings event class properties ********//
	//*****************************************************************//
	/**
	 * @var PageBase
	 * @internal
	 */
	private $page = null;

	/**
	 * Create new instance of object.
	 *
	 * @param PageBase $page
	 * @return void
	 */
	public function __construct(PageBase $page){
		$this->page = $page;
	}

	/**
	 * Get current page.
	 *
	 * @return Page
	 */
	public function getPage(){
		return $this->page;
	}
}
?>