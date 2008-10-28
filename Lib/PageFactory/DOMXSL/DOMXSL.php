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
				if(!PAGE_FACTORY_CACHE_ENABLE || !is_file($this->template_cache_file)){
					$tranformToXML = false;
					if(stristr($this->template->getContentType(), 'xml') || stristr($this->template->getContentType(), 'html')){
						$tranformToXML = true;
					}
					$doc = $proc->transformToDoc($this->xml);
					if(PAGE_FACTORY_CACHE_ENABLE){
						$page = $this->_xslRewrite($doc, $tranformToXML);
					} else {
						$page = $this->_transformPage($doc, $tranformToXML);
					}
					if(!PAGE_FACTORY_CACHE_DEBUG){
						file_put_contents($this->template_cache_file, $page);
					}
				} else {
					$page = file_get_contents($this->template_cache_file);
				}
				if(PAGE_FACTORY_CACHE_ENABLE){
					return PageFactoryDOMXSLCapsule::parseCacheData($page, $this->settings_array, $this->content_array);
				}
				
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
		if(PAGE_FACTORY_CACHE_ENABLE){
			$this->content_array[] = $content->getArray();
		}
		if(!$this->template_cache_file){
			$this->content->appendChild($content->getXML($this->xml));
		}
	}

	public function addPageSettings(Output $settings){
		if(PAGE_FACTORY_CACHE_ENABLE){
			$this->settings_array[] = $settings->getArray();
		}
		if(!$this->template_cache_file){
			$this->settings->appendChild($settings->getXML($this->xml));
		}
	}

	public function getSupportedTemplateDefinition(){
		return __CLASS__;
	}

	public function setTemplate(PageFactoryTemplate $template){
		$return = parent::setTemplate($template);
		if(PAGE_FACTORY_CACHE_ENABLE && is_file(PAGE_FACTORY_CACHE_DIR.'/'.$template->getPageCacheString())){
			$this->template_cache_file = PAGE_FACTORY_CACHE_DIR.'/'.$template->getPageCacheString();
		} else {
			$this->_prepareXML();

			$this->settings->appendChild($this->xml->createElement('script_uri', $this->template->getScriptUri()));
			$this->settings->appendChild($this->xml->createElement('script_url', $this->template->getScriptUrl()));
			$this->settings->appendChild($this->xml->createElement('request_url', str_replace('&', '&amp;', $this->template->getRequestUri())));

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
		}
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

	private function _xslRewrite(DOMDocument $dom, $transformToXML=true){
		$this->_xslRewriteTemplates($dom);

		$this->addParseToken(new PageFactoryDOMXSLParseTokenTemplate());
		$this->addParseToken(new PageFactoryDOMXSLParseTokenDump());
		$this->addParseToken(new PageFactoryDOMXSLParseTokenControlStructure());

		$dom->formatOutput = true;
		$page = $this->_transformPage($dom, true);

		// remove un needed xmlns attributes
		$page = preg_replace('/\s*xmlns:c=".*?"/',
		                     '', $page);

		// Rewrite xml version declaration to a valid php tag
		$page = preg_replace('/\<\?xml(.*?)\?\>/',
		                     '<?php echo \'<?xml\\1?>\'."\n"; ?>', $page);

		while (list(,$val) = each($this->parse_tokens)) {
			$page = $val->parse($page);
		}
		return $page;
	}

	private function _xslRewriteTemplates(DOMDocument $xml){
		$templates = $this->_parseTemplates($this->xsl);
		while (list(,$val) = each($templates[0])) {
			$xml->documentElement->insertBefore($xml->importNode($val, true), $xml->documentElement->firstChild);
		}
		while (list(,$val) = each($templates[1])) {
			$xml->documentElement->insertBefore($xml->importNode($val, true), $xml->documentElement->firstChild);
		}
	}
	private function _parseTemplates(DOMDocument $xsl, $path=null, $call=array(), $match=array()){
		if(is_null($path)){
			$path = getcwd();
		}

		$imports = $xsl->documentElement->getElementsByTagNameNS(PAGE_FACTORY_DOMXSL_XSL_XMLNS, 'import');
		$includes = $xsl->documentElement->getElementsByTagNameNS(PAGE_FACTORY_DOMXSL_XSL_XMLNS, 'include');
		$templates = $xsl->documentElement->getElementsByTagNameNS(PAGE_FACTORY_DOMXSL_CACHE_XMLNS, 'template');

		$stylesheets = array();
		$templateElm = array('match'=>array(), 'name'=>array());

		for ($i = 0; $template = $templates->item($i); $i++){
			$nametpl = $template->getAttribute('name');
			$matchtpl = $template->getAttribute('match');
			if(empty($nametpl) && !empty($matchtpl)){
				$match[$matchtpl] = $template;
			} else if(!empty($nametpl)){
				$call[$nametpl] = $template;
			}
		}

		for ($i = 0; $import = $imports->item($i); $i++){
			$stylesheets[] = $import->getAttribute('href');
		}
		for ($i = 0; $include = $includes->item($i); $i++){
			$stylesheets[] = $include->getAttribute('href');
		}

		while (list(,$val) = each($stylesheets)) {
			$val = $this->_relativeToPath($path, $val);
			$xsl = new DOMDocument('1.0', 'UTF-8');
			$xsl->createElement();
			$xsl->load($val);
			$templates = $this->_parseTemplates($xsl, $val, $call, $match);
			$call = $templates[0];
			$match = $templates[1];
		}
		return array($call, $match);
	}
	private function _relativeToPath($path, $relative){
		if($relative{0} != '/'){
			$filename = basename($relative);
			$cwd = getcwd();
			chdir(dirname($path));
			chdir(dirname($relative));
			$relative = getcwd();
			chdir($cwd);
			return $relative.'/'.$filename;
		} else {
			return $relative;
		}
	}

	private function _xslRewriteParseForeachAs($as){
		if(strstr($as, ',')){
			list($key, $val) = explode(',', $as);
			return '$'.trim($key).', $'.trim($val);
		} else {
			return ', $'.trim($as);
		}
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