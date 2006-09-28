<?php
if(!defined('REDIRECT_URL')){
	define('REDIRECT_URL', 'http://'.$_SERVER['SERVER_NAME'].'/');	
}

class EventApplyDefaultSettings implements Event {
	private $xml = null;
	private $settings = null;

	function __construct(DOMDocument $xml, DOMnode $settings){
		$this->xml = $xml;
		$this->settings = $settings;
	}

	function getXML(){
		return $this->xml;
	}
	function getSettings(){
		return $this->settings;
	}
}

function xmlentities($string, $quote_style=ENT_COMPAT){
   $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);

   foreach ($trans as $key => $value)
       $trans[$key] = '&#'.ord($key).';';

   return strtr($string, $trans);
}

class PageFactory implements Singleton {
	/**
	 *	@var PageFactory
	 */
	private static $instance = null;

	/**
	 *	@var DOMDocument
	 */
	protected $xml = null;
	
	/**
	 *	@var DOMDocument
	 */
	protected $xsl = null;

	private $xml_version = '1.0';
	private $xml_encoding = 'UTF-8';
	private $xsl_core = null;
	
	protected $header = null;
	
	protected $rewrite_engine = false;
	
	static protected $redirect_base = null;
	static protected $no_refer = false;
	private $message_file = null;
	
	const XSL_NAMESPACE_URI = 'http://www.w3.org/1999/XSL/Transform';
	const REFERER_VAR = 'PUBLIC_REFERER';
	const MSGID = 'MSGID';

	private function __construct(){
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

	public function setXMLVersion($version){
		$this->xml_version = $version;
	}
	public function setXMLEncoding($encoding){
		$this->xml_encoding = $encoding;
	}
	
	public function setXSLCore($file){
		$this->xsl_core = $file;
	}

	public function setRewriteEngine($boolean){
		$this->rewrite_engine = $boolean;
	}

	protected function _prepareXML(){
		$this->xml = new DOMDocument($this->xml_version, $this->xml_encoding);
		$this->xml->preserveWhiteSpace = false;
		
		$this->xsl = new DOMDocument($this->xml_version, $this->xml_encoding);
		$this->xsl->preserveWhiteSpace = false;
		
		return $this->xml->appendChild(new DOMElement('page'));
	}

	protected function _setDefaultSettings(DOMNode $settings){
		$settings->appendChild($this->xml->createElement('request_uri', xmlentities($_SERVER['REQUEST_URI'])));	
		if($this->rewrite_engine){
			$settings->appendChild($this->xml->createElement('rewrite_engine'));
		}

		$input = InputHandler::getInstance();
		$settings->appendChild($input->getXML($this->xml));
		
		$session = SessionHandler::getInstance();
		if(!is_null($this->message_file) && $session->check(self::MSGID)){
			$DOMMessages = new DOMDocument('1.0','UTF-8');
			$DOMMessages->load($this->message_file);
			$XPath = new DOMXPath($DOMMessages);
			$DOMMessage = $XPath->query('/messages/message[@id = '.$session->get(self::MSGID).']');
			try {
				if($DOMMessage->length > 1){
					throw new BaseException('Message Collission for messsage('.$session->get(self::MSGID).') ,in message file '.$this->message_file);
				} else if ($DOMMessage->length < 1) {
					throw new BaseException('Non-excisting message('.$session->get(self::MSGID).'), in message file '.$this->message_file);
				} else {
					$DOMMessage = $this->xml->importNode($DOMMessage->item(0), true);
					$settings->appendChild($DOMMessage);
					$session->remove(self::MSGID);
				}
			} catch (BaseException $e){
				echo $e;
			}
		}
		
		$event = EventHandler::getInstance();
		$event->triggerEvent(new EventApplyDefaultSettings($this->xml, $settings));
	}
	
	public function build(Page $page){
		$page->prebuild();
		
		if(!is_null($page->getHeader()))
			$this->header = $page->getHeader();
		
		$DOMpage = $this->_prepareXML();
		$DOMsettings = $DOMpage->appendChild($this->xml->createElement('settings'));
		$DOMhead = $DOMpage->appendChild($this->xml->createElement('head'));
		$DOMcontent = $DOMpage->appendChild($this->xml->createElement('content'));

		
		$this->_setDefaultSettings($DOMsettings);

		
		if(!is_null($page->getXSLTStyleSheetCore())) {
			$this->xsl->load($page->getXSLTStyleSheetCore());
		} else {
			$this->xsl->load($this->xsl_core);
		}
		$XSLStyle = @explode(',',$page->getXSLTStyleSheet());
		while(list(,$val) = each($XSLStyle)){
			$XSLinclude = $this->xsl->documentElement->appendChild($this->xsl->createElementNS(self::XSL_NAMESPACE_URI, 'xsl:include'));
			$XSLinclude->setAttribute('href', $val);
		}
		$xsl = $page->getXSLTStyleSheet();
		if(empty($xsl)){
			$input = InputHandler::getInstance();
			$input->setGet('xml','');
		}
		$page->setDOMDocument($this->xml, $DOMsettings, $DOMpage, $DOMcontent);
		if(method_exists($page, 'parentBuilder')){
			$page->parentBuilder();
		}
		$page->build();
		if(!self::$no_refer){
			$session = SessionHandler::getInstance();
			$session->set(self::REFERER_VAR, $_SERVER['REQUEST_URI']);
		}
	}

	public function draw($content_type = 'text/html; charset=UTF-8'){
		$proc = new XsltProcessor();
		$proc->importStylesheet($this->xsl);
		
		$base = Base::getInstance();
		$input = InputHandler::getInstance();
		if($input->isSetGet('xml') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
			header('Content-Type: text/xml; charset=UTF-8');
			echo $this->xml->saveXML();
		} else if($input->isSetGet('xsl') && BASE_RUNLEVEL == BASE_RUNLEVEL_DEVEL){
			header('Content-Type: text/xml; charset=UTF-8');
			echo $this->xsl->saveXML();
		} else {
			$tranformToXML = false;
			if(!is_null($this->header)){
				header($this->header);
				if(stristr($this->header, 'xml') || stristr($this->header, 'html')){
					$tranformToXML = true;
				}
			} else {
				header("Content-type: $content_type");
				if(stristr($content_type, 'xml') || stristr($content_type, 'html')){
					$tranformToXML = true;
				}
			}
			if($tranformToXML){
				echo $proc->transformToXML($this->xml);
			} else {
				$doc = $proc->transformToDoc($this->xml);
				echo $doc->saveHTML();
			}
		}
	}
	
	/**
	 *	System redirect
	 *	
	 *	This function provides a easy way to redirecting using http
	 *
	 *	@param integer $msgID Current msgID
	 *	@param string $target target URL, if URL is'nt prefixed with http:// the function will add http:// is self.
	 *	@uses contains_http()
	 */
	static public function redirect($msgID=null, $target=null, $append=false, $query=false){
		$session = SessionHandler::getInstance();
		if(!is_null($msgID) && is_integer($msgID)){
			$session->set(self::MSGID, $msgID); 
		}
		
		if(!is_null($target) && !$append){
		} else if(isset($_SERVER['HTTP_REFERER']) || $session->check(self::REFERER_VAR)){
			$append_string = $target;
			if(isset($_SERVER['HTTP_REFERER'])){
				$target = $_SERVER['HTTP_REFERER'];
			} else {
				$target = $session->get(self::REFERER_VAR);
			}
			if($append){
				if($query){
					$list = explode('&', $append_string);
					while(list($key, $var) = each($list)){
						if(strstr($var, '=')){
							list($var, $content) = explode('=', $var, 2);
						}
						$target = preg_replace('/(\?.*?)&{0,1}'.preg_quote($var).'\={0,1}.+?(&|$)/', '\\1\\2',$target);
					}
					if(!strstr($target,'?')){
						$target .= '?';
					} else if(substr($target, -1) != '?'){
						$target .= '&';
					}	
					$target .= $append_string; 
				} else {
					$target = preg_replace('/\?.*$/','',$target);
				}
			}
		} else {
			$target = '';
		}
		try {
			if(is_null(self::$redirect_base)){
				throw new BaseException('self::$redirect_base Not set');
			} else {
				if(!contains_http($target)){
					if(substr($target, 0,1) == '/' && substr(self::$redirect_base, -1) == '/'){
						$target = substr($target,1);
					}
					$target = self::$redirect_base.$target;
				}
				header('Location: '.$target);
				exit;	
			}
		} catch (BaseException $e){
			echo $e;	
		}
	}
	
	public static function set_no_referer(){
		self::$no_refer = true;
	}
	
	public static function setRedirectBase($url){
		self::$redirect_base = $url;
	}

	public function setMessageFile($file){
		$this->message_file = $file;
	}
}

abstract class Page {
	/**
	 *	@var DOMDocument
	 */
	protected $xml = null;
	/**
	 *	@var DOMElement
	 */
	private $page = null;
	/**
	 *	@var DOMElement
	 */
	private $content = null;
	/**
	 *	@var DOMElement
	 */
	private $settings = null;
	
	private $xslt_stylesheet = null;
	
	private $core_xslt_stylesheet = null;
	
	private $header = null;
	
	private $append_settings = array();
	
	public function setDOMDocument(DOMDocument $xml, DOMElement $settings, DOMElement $page, DOMElement $content){
		$this->xml = $xml;
		$this->settings = $settings;
		$this->page = $page;
		$this->content = $content;
		$this->_appendWaitingSettings();
	}

	protected function setXSLTStyleSheet($file){
		$this->xslt_stylesheet = $file;
	}	
	public function getXSLTStyleSheet(){
		return $this->xslt_stylesheet;
	}
	protected function setXSLTStyleSheetCore($file) {
		$this->core_xslt_stylesheet = $file;
	}
	public function getXSLTStyleSheetCore(){
		return $this->core_xslt_stylesheet;
	}
	protected function setHeader($header) {
		$this->header = $header;
	}
	public function getHeader(){
		return $this->header;
	}		
	public function appendSettings(DOMElement $settings){
		if(!$this->settings instanceof DOMElement){
			$this->append_settings[] = $settings;
		} else {
			$this->settings->appendChild($settings);
		}
	}
	
	private function _appendWaitingSettings(){
		while(list(,$val) = each($this->append_settings)){
			$this->appendSettings($this->xml->importNode($val, true));	
		}	
	}
	
	public function appendPage(DOMElement $page){
		$this->page->appendChild($page);
	}

	public function appendContent(DOMElement $content){
		$this->content->appendChild($content);
	}

	public function addCSSStyleSheet($file){
		$this->appendSettings($this->createElement('stylesheet', $file));
	}

	public function addJavaScript($file){
		$this->appendSettings($this->createElement('javascript', $file));
	}
    public function addMetaData($name,$content) {
    	$meta = $this->createElement('meta');
    	$meta->setAttribute('name',$name);
    	$meta->setAttribute('content',$content);
    	$this->appendSettings($meta);
    }
	public function createElement($name, $content=null){
		if(!$this->xml instanceof DOMDocument){
			return new DOMElement($name, $content);
		} else {
			return $this->xml->createElement($name, $content);
		}
	}
	
	abstract public function build();
	abstract public function prebuild();
}
?>