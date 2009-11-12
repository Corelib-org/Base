<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory DOMXSL Template Engine
 *
 * <i>No Description</i>
 *
 * LICENSE: This source file is subject to version 1.0 of the
 * Bravura Distribution license that is available through the
 * world-wide-web at the following URI: http://www.bravura.dk/$/corelib_1_0/.
 * If you did not receive a copy of the Bravura License and are
 * unable to obtain it through the web, please send a note to
 * license@bravura.dk so we can mail you a copy immediately.
 *
 *
 * @author Steffen Sørensen <steffen@bravura.dk>
 * @copyright Copyright (c) 2006 Bravura ApS
 * @license http://www.bravura.dk/licence/corelib_1_0/
 * @package corelib
 * @subpackage Base
 * @link http://www.bravura.dk/
 * @version 1.0.0 ($Id$)
 * @filesource
 */
if(!defined('PAGE_FACTORY_DOMXSL_CACHE_XMLNS')){
	define('PAGE_FACTORY_DOMXSL_CACHE_XMLNS', 'http://www.corelib.org/xmlns/cache');
}
if(!defined('PAGE_FACTORY_DOMXSL_XSL_XMLNS')){
	define('PAGE_FACTORY_DOMXSL_XSL_XMLNS', 'http://www.w3.org/1999/XSL/Transform');
}
if(!defined('PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT')){
	define('PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT', true);
}

class PageFactoryDOMXSL extends PageFactoryTemplateEngine {
	const CACHE_PAGE_XSL = 'Base/Share/Cache.xsl';

	/**
	 *	@var DOMDocument
	 */
	protected $xml = null;
	/**
	 *	@var DOMDocument
	 */
	protected $xsl = null;
	/**
	 * @var DOMElement
	 */
	private $content = null;
	/**
	 * @var Array
	 */
	private $content_array = array();
	/**
	 * @var DOMElement
	 */
	private $settings = null;
	/**
	 * @var Array
	 */
	private $settings_array = array();
	/**
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $template = null;

	/**
	 * @var string
	 */
	private $template_cache_file = false;

	/**
	 * @var Array
	 */
	private $parse_tokens = array();

	public function draw(){
		if($this->page->draw($this)){
			$this->xsl->load($this->template->getCoreXSLT());
			$this->template->buildCoreTemplate($this->xsl);

			$proc = new XsltProcessor();
			$proc->importStylesheet($this->xsl);

			if($functions = $this->template->getRegisteredPHPFunctions()){
				$proc->registerPHPFunctions($functions);
			}

			$input = InputHandler::getInstance();

			if($input->isSetGet('xml') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
				$this->template->setContentType('text/xml');
				$this->template->setContentCharset('UTF-8');
				return $this->xml->saveXML();
			} else {

//				if(!PAGE_FACTORY_CACHE_ENABLE || !is_file($this->template_cache_file)){
					$tranformToXML = false;
					if(stristr($this->template->getContentType(), 'xml') || stristr($this->template->getContentType(), 'html')){
						$tranformToXML = true;
					}
					$doc = $proc->transformToDoc($this->xml);
					if($this->_getCacheType() == PAGE_FACTORY_CACHE_DYNAMIC){
						$page = $this->_transformCachedPage($doc, $tranformToXML);
					} else {
						$page = $this->_transformPage($doc, $tranformToXML);
					}
					/*
					if(!PAGE_FACTORY_CACHE_DEBUG){
						file_put_contents($this->template_cache_file, $page);
					} */
	/*			} else {
					$page = file_get_contents($this->template_cache_file);
				}
				if(PAGE_FACTORY_CACHE_ENABLE){
					return PageFactoryDOMXSLCapsule::parseCacheData($page, $this->settings_array, $this->content_array);
				} */

				$converter = $this->template->getOutputConverter();
				if(!is_null($converter)){
					return $converter->convert($page);
				} else {
					return $page;
				}
			}
		}
	}

	public function addPageContent(Output $content){
		$this->content->appendChild($content->getXML($this->xml));
	}

	public function addPageSettings(Output $settings){
		$this->settings->appendChild($settings->getXML($this->xml));
	}

	public function getSupportedTemplateDefinition(){
		return __CLASS__;
	}

	public function setTemplate(PageFactoryTemplate $template){
		$return = parent::setTemplate($template);
/*		if(PAGE_FACTORY_CACHE_ENABLE && is_file(PAGE_FACTORY_CACHE_DIR.'/'.$template->getPageCacheString())){
			$this->template_cache_file = PAGE_FACTORY_CACHE_DIR.'/'.$template->getPageCacheString();
		} else { */
			$this->_prepareXML();

			$this->settings->appendChild($this->xml->createElement('script_uri', $this->template->getScriptUri()));
			$this->settings->appendChild($this->xml->createElement('script_url', $this->template->getScriptUrl()));
			$this->settings->appendChild($this->xml->createElement('request_url', str_replace('&', '&amp;', $this->template->getRequestUri())));
			$this->settings->appendChild($this->xml->createElement('http_referer', str_replace('&', '&amp;', $this->template->getHTTPReferer())));

			$this->settings->appendChild($this->xml->createElement('server_name', $this->template->getServerName()));
			$this->settings->appendChild($this->xml->createElement('user_agent', $this->template->getUserAgent()));
			$this->settings->appendChild($this->xml->createElement('remote_address', $this->template->getRemoteAddress()));
			$this->settings->appendChild($this->xml->createElement('redirect_url', $this->template->getHTTPRedirectBase()));
			$this->settings->appendChild($this->xml->createElement('base_url', $this->template->getHTTPRedirectBase()));

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
			// $this->settings->appendChild($input->getXML($this->xml));
			if($message = $this->template->getStatusMessage()){
				$this->settings->appendChild($this->xml->importNode($message, true));
			}
		// }
		return $return;
	}

	public function addParseToken(PageFactoryDOMXSLParseToken $token){
		$this->parse_tokens[] = $token;
	}
	private function _transformPage(DOMDocument $dom, $tranformToXML=true){
		$dom->formatOutput = PAGE_FACTORY_DOMXSL_FORMAT_OUTPUT;
		if($tranformToXML){
			return $dom->saveXML();
		} else {
			return $dom->saveHTML();
		}
	}

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

	protected function _prepareXML(){
		$this->xml = new PageFactoryDOMXSLDOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
		$this->xml->preserveWhiteSpace = false;

		$this->xsl = new DOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
		$this->xsl->preserveWhiteSpace = false;

		$page = $this->xml->appendChild(new DOMElement('page'));
		$this->settings = $page->appendChild($this->xml->createElement('settings'));
		$this->content = $page->appendChild($this->xml->createElement('content'));
		return true;
	}

}

class PageFactoryDOMXSLDOMDocument extends DOMDocument {
	public function createElement($name, $value=null){
		if(!is_null($value)){
			return parent::createElement($name, XMLTools::escapeXMLCharacters($value));
		} else {
			return parent::createElement($name);
		}
	}
}
?>