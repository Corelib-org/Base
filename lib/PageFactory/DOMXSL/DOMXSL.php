<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page factory DOMXSL Template engine.
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
 * @version 1.0.0 ($Id$)
 * @todo impliment XSLT profiling and developer toolbar item
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('PAGE_FACTORY_DOMXSL_CACHE_XMLNS')){
	/**
	 * Cache xml namespace.
	 *
	 * @var string url
	 */
	define('PAGE_FACTORY_DOMXSL_CACHE_XMLNS', 'http://www.corelib.org/xmlns/cache');
}

if(!defined('PAGE_FACTORY_DOMXSL_XSL_XMLNS')){
	/**
	 * XSL xml Namespace.
	 *
	 * @var string url
	 */
	define('PAGE_FACTORY_DOMXSL_XSL_XMLNS', 'http://www.w3.org/1999/XSL/Transform');
}

if(!defined('PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT')){
	/**
	 * Format xml output.
	 *
	 * @var boolean true to format, else false for no formatting
	 */
	define('PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT', true);
}


//*****************************************************************//
//******************* PageFactoryDOMXSL class *********************//
//*****************************************************************//
/**
 * Page Factory DOMXSL template engine.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
class PageFactoryDOMXSL extends PageFactoryTemplateEngine {


	//*****************************************************************//
	//************** PageFactoryDOMXSL class properties ***************//
	//*****************************************************************//
	/**
	 * @var DOMDocument xml document
	 * @internal
	 */
	protected $xml = null;

	/**
	 * @var DOMDocument XSL document
	 * @internal
	 */
	protected $xsl = null;

	/**
	 * @var DOMElement content element.
	 * @internal
	 */
	private $content = null;

	/**
	 * @var Array content objects
	 * @internal
	 */
	private $content_array = array();

	/**
	 * @var DOMElement settings element
	 * @internal
	 */
	private $settings = null;

	/**
	 * @var array content objects
	 * @internal
	 */
	private $settings_array = array();

	/**
	 * @var PageFactoryDOMXSLTemplate
	 * @internal
	 */
	protected $template = null;

	/**
	 * @var string template cache file
	 * @internal
	 */
	private $template_cache_file = false;


	//*****************************************************************//
	//******************* PageFactoryDOMXSL class *********************//
	//*****************************************************************//
	/**
	 * Cache template.
	 *
	 * @var string xsl file
	 * @internal
	 */
	const CACHE_PAGE_XSL = 'Base/share/xsl/cache.xsl';


	//*****************************************************************//
	//******************* PageFactoryDOMXSL class *********************//
	//*****************************************************************//
	/**
	 * Draw page content.
	 *
	 * @see PageFactoryTemplateEngine::draw()
	 */
	public function draw(){
		if($this->page->draw($this)){
			$this->xsl->load($this->template->getCoreXSLT());
			$this->template->buildCoreTemplate($this->xsl);

			$proc = new XsltProcessor();
			$proc->importStylesheet($this->xsl);
			// $proc->setProfiling('var/db/profile.txt');

			if($functions = $this->template->getRegisteredPHPFunctions()){
				$proc->registerPHPFunctions($functions);
			}

			$input = InputHandler::getInstance();

			if($input->isSetGet('xml') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
				$this->template->setContentType('text/xml');
				$this->template->setContentCharset('utf-8');
				return $this->xml->saveXML();
			} else {

				$tranformToXML = false;
				if(stristr($this->template->getContentType(), 'xml') || stristr($this->template->getContentType(), 'html')){
					$tranformToXML = true;
				}
				Logger::debug('Detected caching type: '.$this->_getCacheType());
				$doc = $proc->transformToDoc($this->xml);
				if($this->_getCacheType() == PAGE_FACTORY_CACHE_DYNAMIC){
					$page = $this->_transformCachedPage($doc, $tranformToXML);
				} else {
					$page = $this->_transformPage($doc, $tranformToXML);
				}
				Logger::debug('Rendered page from xml and xsl');


				$converter = $this->template->getOutputConverter();
				if(!is_null($converter)){
					return $converter->convert($page);
				} else {
					return $page;
				}
			}
		}
	}

	/**
	 * Add page content to page.
	 *
	 * @see PageFactoryTemplateEngine::addPageContent()
	 */
	public function addPageContent(Output $content){
		$this->content->appendChild($content->getXML($this->xml));
		return true;
	}

	/**
	 * Add page settings to page.
	 *
	 * @see PageFactoryTemplateEngine::addPageSettings()
	 */
	public function addPageSettings(Output $settings){
		$this->settings->appendChild($settings->getXML($this->xml));
	}

	/**
	 * Set current active template.
	 *
	 * @see PageFactoryTemplateEngine::setTemplate()
	 */
	public function setTemplate(PageFactoryTemplate $template){
		assert('$template instanceof PageFactoryDOMXSLTemplate');
		$return = parent::setTemplate($template);
		$this->_prepareXML();

		$this->settings->appendChild($this->xml->createElement('script-uri', $this->template->getScriptUri()));
		$this->settings->appendChild($this->xml->createElement('script-url', $this->template->getScriptUrl()));
		$this->settings->appendChild($this->xml->createElement('request-url', str_replace('&', '&amp;', $this->template->getRequestUri())));
		$this->settings->appendChild($this->xml->createElement('http-referer', str_replace('&', '&amp;', $this->template->getHTTPReferer())));

		$this->settings->appendChild($this->xml->createElement('server-name', $this->template->getServerName()));
		$this->settings->appendChild($this->xml->createElement('user-agent', $this->template->getUserAgent()));
		$this->settings->appendChild($this->xml->createElement('remote-address', $this->template->getRemoteAddress()));
		$this->settings->appendChild($this->xml->createElement('redirect-url', $this->template->getHTTPRedirectBase()));
		$this->settings->appendChild($this->xml->createElement('base-url', $this->template->getHTTPRedirectBase()));

		$stylesheets = $this->template->getStyleSheets();
		while(list(,$val) = each($stylesheets)){
			$this->settings->appendChild($this->xml->createElement('stylesheet', $val));
		}
		$javascripts = $this->template->getJavaScripts();
		while(list(,$val) = each($javascripts)){
			$this->settings->appendChild($this->xml->createElement('javascript', $val));
		}

		$input = InputHandler::getInstance();
		$this->addPageSettings($input);
		if($message = $this->template->getStatusMessage()){
			$this->settings->appendChild($this->xml->importNode($message, true));
		}
		return $return;
	}

	/**
	 * Transform content XML.
	 *
	 * @param DOMDocument $dom
	 * @param boolean $tranformToXML true for xml compatible output, else false
	 * @return string content
	 * @internal
	 */
	private function _transformPage(DOMDocument $dom, $tranformToXML=true){
		$dom->formatOutput = PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT;
		if($tranformToXML){
			return $dom->saveXML();
		} else {
			return $dom->saveHTML();
		}
	}

	/**
	 * Transform page to cachable page.
	 *
	 * @param DOMDocument $dom
	 * @param boolean $tranformToXML true for xml compatible output, else false
	 * @return string cached page.
	 * @internal
	 */
	private function _transformCachedPage(DOMDocument $dom, $tranformToXML=true){
		$xsl = new DOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
		$xsl->preserveWhiteSpace = false;
		$xsl->load(CORELIB.'/'.self::CACHE_PAGE_XSL);

		$proc = new XsltProcessor();
		$proc->importStylesheet($xsl);
		$proc->registerPHPFunctions(__CLASS__.'::_rewriteCPath');

		$doc = $proc->transformToDoc($dom);
		$doc = $this->_transformPage($doc, $tranformToXML);
		$code = preg_replace('/^(.*?)\n/s', '<?php echo \'\\1\'."\n"; ?>', $doc);
		return $code;

	}

	/**
	 * Rewrite compatible content path.
	 *
	 * @param string $path
	 * @return string php eval string
	 * @internal
	 */
	public static function _rewriteCPath($path){
		if(is_array($path)){
			$path = $path[0];
		}
		if($path instanceof DOMAttr){
			$path = $path->value;
		}

		$return = '';
		$path = explode('/', $path);
		if(empty($path[0])){
			if(preg_match('/\[([0-9]+)\]/', $path[2], $match)){
				$location = $match[1];
			} else {
				$location = 0;
			}
			$return .= '$this->'.$path[1].'[\''.preg_replace('/\[[0-9]+\]/', '', $path[2]).'\']['.$location.']';
			array_shift($path);
			array_shift($path);
			array_shift($path);
		} else {
			$return .= '$current';
		}
		foreach($path as $item){
			$return .= '->get'.$item.'()';
		}


		return $return;
	}

	/**
	 * Prepare output XML Document.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	protected function _prepareXML(){
		$this->xml = new PageFactoryDOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
		$this->xml->preserveWhiteSpace = false;

		$this->xsl = new DOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
		$this->xsl->preserveWhiteSpace = false;

		$page = $this->xml->appendChild(new DOMElement('page'));
		$this->settings = $page->appendChild($this->xml->createElement('settings'));
		$this->content = $page->appendChild($this->xml->createElement('content'));
		return true;
	}
}
?>