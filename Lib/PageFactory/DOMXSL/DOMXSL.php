<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory DOMXSL Template Engine
 *
 * <i>No Description</i>
 *
 * LICENSE: This source file is subject to version 1.0 of the 
 * Bravura Distribution license that is available through the 
 * world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
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
	 * @var DOMElement
	 */
	private $settings = null;
	/**
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $template = null;
	
	public function draw(){
		if($this->page->draw($this)){
			$this->xsl->load(DOMXSL_TEMPLATE_XSL_CORE);
			$this->template->buildCoreTemplate($this->xsl);
			
			$proc = new XsltProcessor();
			$proc->importStylesheet($this->xsl);

			$input = InputHandler::getInstance();
			
			if($input->isSetGet('xml') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
				$this->template->setContentType('text/xml');
				$this->template->setContentCharset('UTF-8');
				echo $this->xml->saveXML();
			} else {
				$tranformToXML = false;
				if(stristr($this->template->getContentType(), 'xml') || stristr($this->template->getContentType(), 'html')){
					$tranformToXML = true;
				} 
				if($tranformToXML){
					echo $proc->transformToXML($this->xml);
				} else {
					$doc = $proc->transformToDoc($this->xml);
					echo $doc->saveHTML();
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
		$this->_prepareXML();
		
		$this->settings->appendChild($this->xml->createElement('script_uri', $this->template->getScriptUri()));
		$this->settings->appendChild($this->xml->createElement('script_url', $this->template->getScriptUrl()));
		$this->settings->appendChild($this->xml->createElement('request_url', str_replace('&', '&amp;', $this->template->getRequestUri())));
		
		$this->settings->appendChild($this->xml->createElement('server_name', $this->template->getServerName()));
		$this->settings->appendChild($this->xml->createElement('user_agent', $this->template->getUserAgent()));
		$this->settings->appendChild($this->xml->createElement('remote_address', $this->template->getRemoteAddress()));
		$this->settings->appendChild($this->xml->createElement('redirect_url', $this->template->getHTTPRedirectBase()));	
		
		$stylesheets = $this->template->getStyleSheets();
		while(list(,$val) = each($stylesheets)){
			$this->settings->appendChild($this->xml->createElement('stylesheet', $val));
		}
		$javascripts = $this->template->getJavaScripts();
		while(list(,$val) = each($javascripts)){
			$this->settings->appendChild($this->xml->createElement('javascript', $val));
		}
		
		$input = InputHandler::getInstance();
		$this->settings->appendChild($input->getXML($this->xml));
		if($message = $this->template->getStatusMessage()){
			$this->settings->appendChild($this->xml->importNode($message, true));
		}
		return $return;
	}
	
	protected function _prepareXML(){
		$this->xml = new DOMDocument($this->template->getXMLVersion(), $this->template->getXMLEncoding());
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