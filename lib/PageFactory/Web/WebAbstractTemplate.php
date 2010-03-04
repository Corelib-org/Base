<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page factory web abstract template.
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
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('BASE_URL')){
	/**
	 * Define Redirect Base URL.
	 *
	 * @var string base url
	 */
	define('BASE_URL', 'http://'.$_SERVER['SERVER_NAME'].'/');

	Base::getInstance()->loadClass('WebInteralLoopbackStream');
}


//*****************************************************************//
//************ PageFactoryWebAbstractTemplate class ***************//
//*****************************************************************//
/**
 * Page factory abstract web template class.
 *
 * This class provides some basic http tools and methods
 * this class can be extended other http based templates.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
abstract class PageFactoryWebAbstractTemplate extends PageFactoryTemplate {


	//*****************************************************************//
	//******* PageFactoryWebAbstractTemplate class properties *********//
	//*****************************************************************//
	/**
	 * Last modified timestamp header
	 *
	 * @var integer unix timestamp
	 * @internal
	 */
	private $last_modified = null;

	/**
	 * Expires timestamp header
	 *
	 * @var integer unix timestamp
	 * @internal
	 */
	private $expires = null;

	/**
	 * Cache control header
	 *
	 * @var string cache control header
	 * @internal
	 */
	private $cache_control = null;

	/**
	 * Pragma header.
	 *
	 * @var string pragma header
	 * @internal
	 */
	private $pragma = null;

	/**
	 * Content-Type header.
	 *
	 * @var string content mime-type
	 * @internal
	 */
	private $content_type = 'text/html';

	/**
	 * Content charset.
	 *
	 * @var string characterset
	 * @internal
	 */
	private $content_charset = 'UTF-8';

	/**
	 * Location header.
	 *
	 * @var string location header
	 * @internal
	 */
	private $location = null;

	/**
	 * Message ID.
	 *
	 * @var integer message id.
	 * @internal
	 */
	private $message_id = null;

	/**
	 * Script URL.
	 *
	 * @var string script url
	 * @internal
	 */
	private $script_url = null;

	/**
	 * Script URI.
	 *
	 * @var string script uri
	 * @internal
	 */
	private $script_uri = null;

	/**
	 * Request URI.
	 *
	 * @var string request uri
	 * @internal
	 */
	private $request_uri = null;

	/**
	 * HTTP Referer.
	 *
	 * @var string http referer
	 * @internal
	 */
	private $http_referer = null;

	/**
	 * Remote user ip address.
	 *
	 * @var string remote user address
	 * @internal
	 */
	private $remote_addr = null;

	/**
	 * User Agent.
	 *
	 * @var string user agent
	 * @internal
	 */
	private $user_agent = null;

	/**
	 * Server name.
	 *
	 * @var string server name
	 * @internal
	 */
	private $server_name = null;

	/**
	 * List of stylesheets.
	 *
	 * @var array stylesheets
	 * @internal
	 */
	private $stylesheets = array();

	/**
	 * list of Javascripts.
	 *
	 * @var array javascripts
	 * @internal
	 */
	private $javascripts = array();

	/**
	 * Redirect base.
	 *
	 * @var string http redirect base
	 * @see BASE_URL
	 * @internal
	 */
	private $http_redirect_base = null;

	/**
	 * Set http referer.
	 *
	 * @var boolean set http referer
	 * @internal
	 */
	private $set_referer = true;


	//*****************************************************************//
	//******** PageFactoryWebAbstractTemplate class constants *********//
	//*****************************************************************//
	/**
	 * Session name for referer.
	 *
	 * @var string
	 * @internal
	 */
	const REFERER_VAR = 'PUBLIC_REFERER';

	/**
	 * Session name for message ID.
	 *
	 * @var string
	 * @internal
	 */
	const MSGID = 'MSGID';


	//*****************************************************************//
	//********* PageFactoryWebAbstractTemplate class methods **********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * When a new instane is created the object will be populated with various
	 * data from the http request.
	 *
	 * @return void
	 */
	public function __construct(){
		if(!defined('HTTP_STATUS_MESSAGE_FILE')){
			/**
			 * HTTP status message file.
			 *
			 * @var string filename
			 * @internal
			 */
			define('HTTP_STATUS_MESSAGE_FILE', 'share/messages.xml');
		}
		if(isset($_SERVER['SCRIPT_URL'])){
			$this->script_url = $_SERVER['SCRIPT_URL'];
		}
		if(isset($_SERVER['SCRIPT_URI'])){
			$this->script_uri = $_SERVER['SCRIPT_URI'];
		}
		$this->request_uri = $_SERVER['REQUEST_URI'];

		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		if(isset($_SERVER['HTTP_REFERER'])){
			$this->http_referer = $_SERVER['HTTP_REFERER'];
		}

		$this->remote_addr = $_SERVER['REMOTE_ADDR'];
		$this->server_name = $_SERVER['SERVER_NAME'];

		$this->http_redirect_base = BASE_URL;
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->set_referer = false;
		}
	}

	/**
	 * Template init method.
	 *
	 * Init template and check to see if a location header
	 * should be sent. if a locations header should is to be
	 * sent return false and prevent any further output.
	 *
	 * @see PageFactoryTemplate::init()
	 * @return boolean true of no locations header is sent, else return false
	 */
	public function init(){
		ob_start();
		return is_null($this->location);
	}

	/**
	 * Cleanup template before sending output.
	 *
	 * Send all http headers before sending output.
	 *
	 * @see PageFactoryTemplate::cleanup()
	 */
	public function cleanup(){
		$session = SessionHandler::getInstance();
		if(!is_null($this->message_id)){
			$session->set(self::MSGID, $this->message_id);
		}
		if($this->set_referer){
			$session->set(self::REFERER_VAR, $this->request_uri);
		}

		if(!is_null($this->last_modified)){
			header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T'), $this->last_modified);
		}
		if(!is_null($this->expires)){
			header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $this->expires));
			header('Cache-Control: private, no-store');
			header('Pragma:');
		}

		if(is_null($this->location)){
			header('Content-Location: '. $this->request_uri);

			$type = $this->content_type;
			if(!is_null($this->content_charset)){
				$type .= '; charset='.$this->content_charset;
			}
			header('Content-Type: '.$type);
			ob_end_flush();
		} else {
			header('Location: '.$this->location);
			ob_end_clean();
		}
	}

	/**
	 * Set Last-Modified header.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.29
	 * @param integer $timestamp unix timestamp
	 * @return boolean true on success, else return false.
	 */
	public function setLastModified($timestamp){
		assert('is_integer($timestamp)');
		return ($this->last_modified = $timestamp);
	}

	/**
	 * Set Expires header.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21
	 * @param integer $timestamp unix timestamp
	 * @return boolean true on success, else return false.
	 */
	public function setExpire($timestamp){
		assert('is_integer($timestamp)');
		return ($this->expires = $timestamp);
	}

	/**
	 * Set Content-Type header.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.17
	 * @param string $content_type content mime-type
	 * @return boolean true on success, else return false
	 */
	public function setContentType($content_type='text/html'){
		assert('is_string($content_type)');
		return ($this->content_type = $content_type);
	}

	/**
	 * Set content character set.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.17
	 * @param string $charset
	 * @return boolean true on success, else return false
	 */
	public function setContentCharset($charset='UTF-8'){
		assert('is_string($charset)');
		return ($this->content_charset = $charset);
	}

	/**
	 * Set Location header.
	 *
	 * When the Location header is set the template will
	 * make a HTTP redirect using the Location header.
	 *
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.30
	 * @param string $location address
	 * @param string $param additional parameters
	 * @return boolean true on success, else return false
	 */
	public function setLocation($location, $param=null){
		if($location{0} != '/' && !preg_match('/^http:/', $location)){
			$location = '/'.$location;
		}
		if(!is_null($param)){
			if(strstr($this->http_redirect_base.$location, '?')){
				$param = '&'.$param;
			} else {
				$param = '?'.$param;
			}
		}
		if(preg_match('/^http:\/\//', $location) || preg_match('/^https:\/\//', $location)){
			$this->location = $location;
		} else {
			$this->location = $this->http_redirect_base.$location.$param;
		}

		$this->location = str_ireplace('//', '/', $this->location);
		$this->location = str_ireplace('http:/', 'http://', $this->location);
		return true;
	}

	/**
	 * Set action message ID.
	 *
	 * When setting a message id. the id will be stored
	 * for the next request. when the request is being handled
	 * it is possible to get a status message using
	 * {@link PageFactoryWebAbstractTemplate::getStatusMessage()}
	 * which then will return a dom node container a xml description
	 * of that status message.
	 *
	 * @param integer $id message id
	 * @return boolean true on success, else return false
	 */
	public function setMessageID($id){
		assert('is_integer($id)');
		return ($this->message_id = $id);
	}

	/**
	 * Enforce SSL.
	 *
	 * If SSL is enforced a check is made to see of
	 * a https connection is used. if not a automatic
	 * redirect will be made to a https address.
	 *
	 * @return boolean true if connections a https connections, else return false
	 */
	public function setForceSSL(){
		if(!isset($_SERVER['HTTPS'])){
			$this->setLocation(str_replace('http://', 'https://', BASE_URL).$_SERVER['REQUEST_URI']);
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Add javascript.
	 *
	 * @param string $javascript uri to javascript file.
	 * @return boolean true on success, else return false
	 */
	public function addJavaScript($javascript){
		assert('is_string($javascript)');
		return ($this->javascripts[] = $javascript);
	}

	/**
	 * Add stylesheet.
	 *
	 * @param string $stylesheet uri to stylesheet file
	 * @return boolean true on success, else return false
	 */
	public function addStyleSheet($stylesheet){
		assert('is_string($stylesheet)');
		$this->stylesheets[] = $stylesheet;
	}

	/**
	 * Get javascripts.
	 *
	 * @return array list of all javascripts
	 */
	public function getJavaScripts(){
		return $this->javascripts;
	}

	/**
	 * Get stylesheets.
	 * @return array list of all stylesheets
	 */
	public function getStyleSheets(){
		return $this->stylesheets;
	}

	/**
	 * Get Script URL.
	 *
	 * @return string
	 */
	public function getScriptUrl(){
		return $this->script_url;
	}

	/**
	 * Get message ID
	 *
	 * @return integer
	 */
	public function getMessageID(){
		return $this->message_id;
	}

	/**
	 * Get Script URI.
	 *
	 * @return string
	 */
	public function getScriptUri(){
		return $this->script_uri;
	}

	/**
	 * Get Request URI.
	 *
	 * @return string
	 */
	public function getRequestUri(){
		return $this->request_uri;
	}

	/**
	 * Get user agent.
	 *
	 * @return string
	 */
	public function getUserAgent(){
		return $this->user_agent;
	}

	/**
	 * Get Remote clients' ip address.
	 *
	 * @return string ip address
	 */
	public function getRemoteAddress(){
		return $this->remote_addr;
	}

	/**
	 * Get server name.
	 *
	 * @return string
	 */
	public function getServerName(){
		return $this->server_name;
	}

	/**
	 * Get Content-Type.
	 *
	 * @return string mime-type
	 */
	public function getContentType(){
		return $this->content_type;
	}

	/**
	 * Get HTTP Redirect base.
	 *
	 * @return string url
	 */
	public function getHTTPRedirectBase(){
		return $this->http_redirect_base;
	}

	/**
	 * Get http referer.
	 *
	 * @return string url
	 */
	public function getHTTPReferer(){
		return $this->http_referer;
	}

	/**
	 * Get status message.
	 *
	 * Return DOMElement containing the current status message.
	 *
	 * @return DOMElement
	 */
	public function getStatusMessage(){
		$session = SessionHandler::getInstance();
		if($session->check(self::MSGID)){
			$DOMMessages = new DOMDocument('1.0','UTF-8');
			$DOMMessages->load(HTTP_STATUS_MESSAGE_FILE);
			$XPath = new DOMXPath($DOMMessages);
			$DOMMessage = $XPath->query('/messages/message[@id = '.$session->get(self::MSGID).']');
			try {
				if($DOMMessage->length > 1){
					throw new BaseException('Message Collission for messsage('.$session->get(self::MSGID).') ,in message file '.HTTP_STATUS_MESSAGE_FILE);
				} else if ($DOMMessage->length < 1) {
					// throw new BaseException('Non-excisting message('.$session->get(self::MSGID).'), in message file '.HTTP_STATUS_MESSAGE_FILE);
				} else {
					$session->remove(self::MSGID);
					return $DOMMessage->item(0);
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
		}
		return false;
	}
}


//*****************************************************************//
//***************** PageFactoryPostTemplate class *****************//
//*****************************************************************//
/**
 * Page factory post web template class.
 *
 * This template is supposed to be used to handle post requests.
 * and therefore it is a no output template.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class PageFactoryPostTemplate extends PageFactoryWebAbstractTemplate {


	//*****************************************************************//
	//*********** PageFactoryPostTemplate class constants *************//
	//*****************************************************************//
	/**
	 * Supported template engine.
	 *
	 * @var string template engine
	 * @internal
	 */
	const TEMPLATE_ENGINE = 'PageFactoryPost';


	//*****************************************************************//
	//************ PageFactoryPostTemplate class methods **************//
	//*****************************************************************//
	/**
	 * Get supported template engine name.
	 *
	 * @see PageFactoryTemplate::getSupportedTemplateEngineName()
	 * @internal
	 */
	public function getSupportedTemplateEngineName(){
		return self::TEMPLATE_ENGINE;
	}
}


//*****************************************************************//
//********************** PageFactoryPost class ********************//
//*****************************************************************//
/**
 * Page factory post engine class.
 *
 * This template is supposed to be used to handle post requests.
 * and therefore it is a no output template engine.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class PageFactoryPost extends PageFactoryTemplateEngine {
	/**
	 * Draw page.
	 *
	 * @see PageFactoryTemplateEngine::draw()
	 * @return string
	 */
	public function draw(){
		$this->page->draw($this);
		return '';
	 }

	 /**
	  * Add page content.
	  *
	  * Since this template engine is a no-output template engine
	  * this method is a dummy method.
	  *
	  * @see PageFactoryTemplateEngine::addPageContent()
	  */
	public function addPageContent(Output $content){ return true; }

	 /**
	  * Add page setttings.
	  *
	  * Since this template engine is a no-output template engine
	  * this method is a dummy method.
	  *
	  * @see PageFactoryTemplateEngine::addPageSettings()
	  */
	public function addPageSettings(Output $settings){ return true; }
}
?>
