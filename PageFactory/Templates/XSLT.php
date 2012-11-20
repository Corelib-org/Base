<?php
namespace Corelib\Base\PageFactory\Templates;

class XSLT extends HTTP {

	/**
	 * @var string XML Version
	 * @internal
	 */
	private $xml_version = '1.0';

	/**
	 * @var string XML encoding
	 * @internal
	 */
	private $xml_encoding = 'utf-8';

	/**
	 * @var null|\PageFactoryDOMDocument
	 */
	private $xml = null;

	private $xsl_core = null;

	/**
	 * @var null|\DOMElement
	 */
	private $settings = null;

	/**
	 * @var null|\DOMElement
	 */
	private $content = null;

	/**
	 * @var array
	 */
	private $templates = array();

	private $stylesheets = array();

	private $javascripts = array();


	private $registered_php_functions = array();

	/**
	 * @var XSL Namespace URI
	 * @internal
	 */
	const XSL_NAMESPACE_URI = 'http://www.w3.org/1999/XSL/Transform';


	/**
	 * Create new instance.
	 *
	 * @param string $xslcore xsl core filename
	 * @return void
	 */
	public function __construct($xslcore = null){
		parent::__construct();

		if(!defined('DOMXSL_TEMPLATE_XSL_PATH')){
			/**
			 * XSLT template path.
			 *
			 * Where to look for the xsl templates
			 *
			 * @var string directory
			 */
			define('DOMXSL_TEMPLATE_XSL_PATH', CURRENT_WORKING_DIR.'share/xsl/');
		}

		if(is_null($xslcore)){
			$xslcore = DOMXSL_TEMPLATE_XSL_PATH.'base/core.xsl';
		} else if ($xslcore{0} != '/'){
			$xslcore = DOMXSL_TEMPLATE_XSL_PATH.$xslcore;
		}
		$this->xsl_core = $xslcore;

		//
		// IE Hack.
		//
		// Internet explorer does'nt understand the application/xhtml+xml header
		// instead just send a normal text/html header for clients using internet
		// explorer.
		//
		if(strstr($this->getUserAgent(), 'MSIE')){
			$this->setContentType('text/html');
		} else {
			$this->setContentType('application/xhtml+xml');
		}
	}

	/**
	 * Set XML Version
	 *
	 * @param string $version
	 * @return boolean true on success, else return false
	 */
	public function setXMLVersion($version){
		$this->xml_version = $version;
		return true;
	}

	/**
	 * Set XML Character encoding.
	 *
	 * @param $encoding
	 * @return boolean true on success, else return false
	 */
	public function setXMLEncoding($encoding){
		$this->xml_encoding = $encoding;
		return true;
	}

	public function addTemplate($template_file, $unshift=false){

		if($template_file{0} == '/' || preg_match('/^[a-zA-Z]:/', $template_file)){
			$template = $template_file;
		} else {
			if(!$template = realpath(DOMXSL_TEMPLATE_XSL_PATH.$template_file)){
				trigger_error('XSL Template file not found: '.DOMXSL_TEMPLATE_XSL_PATH.$template_file, E_USER_ERROR);
				return false;
			}
		}
		if(!$unshift){
			$this->templates[] = $template;
		} else {
			array_unshift($this->templates, $template);
		}

		return true;
	}

	/**
	 * Get XML Version.
	 *
	 * @return string XML Version
	 * @see PageFactoryDOMXSLTemplate::setXMLVersion()
	 */
	public function getXMLVersion(){
		return $this->xml_version;
	}

	/**
	 * Get XML character encoding.
	 *
	 * @return string XML encoding
	 * @see PageFactoryDOMXSLTemplate::setXMLEncoding()
	 */
	public function getXMLEncoding(){
		return $this->xml_encoding;
	}


	public function addContent($content){
		if($content instanceof \Corelib\Base\PageFactory\Output || $content instanceof \Output){
			$this->content->appendChild($content->getXML($this->xml));
		} else {
			throw new \Exception('Content is not of instance \Corelib\Base\PageFactory\Output or \Output');
		}

	}

	public function addSettings($settings){
		if($settings instanceof \Corelib\Base\PageFactory\Output || $settings instanceof \Output){
			$this->settings->appendChild($settings->getXML($this->xml));
		} else {
			throw new \Exception('Setting is not of instance \Corelib\Base\PageFactory\Output or \Output');
		}

	}

	/**
	 * Register PHP functions.
	 *
	 * Register a number of php functions which
	 * should be exportet to XSLT.
	 *
	 * @param array $functions list of php function
	 * @return boolean true on success, else return false
	 * @todo make this work
	 */
	public function registerPHPFunctions(array $functions){
		$this->registered_php_functions = $functions;
		return true;
	}


	public function prepare(){
		if(parent::prepare()){
			$this->xml = new \PageFactoryDOMDocument($this->xml_version, $this->xml_encoding);
			$this->xml->preserveWhiteSpace = false;

			$page = $this->xml->appendChild(new \DOMElement('page'));
			$this->settings = $page->appendChild($this->xml->createElement('settings'));
			$this->content = $page->appendChild($this->xml->createElement('content'));

			$this->settings->appendChild($this->xml->createElement('script-uri', $this->getScriptUri()));
			$this->settings->appendChild($this->xml->createElement('script-url', $this->getScriptUrl()));
			$this->settings->appendChild($this->xml->createElement('request-url', str_replace('&', '&amp;', $this->getRequestUri())));
			$this->settings->appendChild($this->xml->createElement('http-referer', str_replace('&', '&amp;', $this->getHTTPReferer())));

			$this->settings->appendChild($this->xml->createElement('server-name', $this->getServerName()));
			$this->settings->appendChild($this->xml->createElement('user-agent', $this->getUserAgent()));
			$this->settings->appendChild($this->xml->createElement('remote-address', $this->getRemoteAddress()));
			$this->settings->appendChild($this->xml->createElement('redirect-url', $this->getHTTPRedirectBase()));
			$this->settings->appendChild($this->xml->createElement('base-url', $this->getHTTPRedirectBase()));

			while(list(,$val) = each($this->stylesheets)){
				$this->settings->appendChild($this->xml->createElement('stylesheet', $val));
			}
			while(list(,$val) = each($this->javascripts)){
				$this->settings->appendChild($this->xml->createElement('javascript', $val));
			}

			$input = \InputHandler::getInstance();
			$this->addSettings($input);
			if($message = $this->getStatusMessage()){
				$this->settings->appendChild($this->xml->importNode($message, true));
			}
			return true;
		}
		return false;
	}

	public function render(){
		$xsl = new \DOMDocument($this->xml_version, $this->xml_encoding);
		$xsl->preserveWhiteSpace = false;
		$xsl->load($this->xsl_core);

		while(list(,$val) = each($this->templates)){
			$XSLinclude = $xsl->documentElement->appendChild($xsl->createElementNS(self::XSL_NAMESPACE_URI, 'xsl:include'));
			$XSLinclude->setAttribute('href', $val);
		}

		$proc = new \XsltProcessor();
		$proc->importStylesheet($xsl);
		// $proc->setProfiling('var/db/profile.txt');
 		$proc->registerPHPFunctions($this->registered_php_functions);

		$input = \InputHandler::getInstance();

		if($input->isSetGet('xml') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
			$this->setContentType('text/xml');
			$this->setContentCharset('utf-8');
			return $this->xml->saveXML();
		} else {
			$tranformToXML = false;
			if(stristr($this->getContentType(), 'xml') || stristr($this->getContentType(), 'html')){
				$tranformToXML = true;
			}
			$doc = $proc->transformToDoc($this->xml);

			$doc->formatOutput = true;
			if($tranformToXML){
				$page = $doc->saveXML();
			} else {
				$page = $doc->saveHTML();
			}
			return $page;
		}
	}
}