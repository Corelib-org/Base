<?php
namespace Corelib\Base\PageFactory\Templates;

use Corelib\Base\PageFactory\Template,
	Corelib\Base\ErrorHandler,
	Corelib\Base\ServiceLocator\Locator,
	Corelib\Base\Exception,
	DOMDocument,
	DOMXPath;

if(!defined('BASE_URL')){
	/**
	 * Define Redirect Base URL.
	 *
	 * @var string base url
	 */
	define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['SERVER_NAME'].'/');
}


abstract class HTTP extends Template {

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
	 * HTTP Status code
	 *
	 * @var string http status code
	 * @internal
	 */
	private $status_code = '200 OK';

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

	/**
	 * Session name for message ID.
	 *
	 * @var string
	 * @internal
	 */
	const MSGID = 'MSGID';

	/**
	 * Session name for referer.
	 *
	 * @var string
	 * @internal
	 */
	const REFERER_VAR = 'PUBLIC_REFERER';

	/**
	 * Create new instance.
	 *
	 * When a new instane is created the object will be populated with various
	 * data from the http request.
	 *
	 * @return void
	 */
	public function __construct(){
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
	 * Set HTTP Status code and message.
	 *
	 * @param int $code HTTP Status code
	 * @param string $message HTTP Status message
	 * @return boolean true on success, else return false
	 */
	public function setStatusCode($code, $message=''){
		$this->status_code = $code.' '.$message;
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
		if(empty($location)){
			$location = '/';
		}
		if($location{0} != '/' && !preg_match('/^http(s)?:/', $location)){
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
			$this->location = $location.$param;
		} else {
			$this->location = $this->http_redirect_base.$location.$param;
		}

		$this->location = str_ireplace('//', '/', $this->location);
		$this->location = str_ireplace('http:/', 'http://', $this->location);
		$this->location = str_ireplace('https:/', 'https://', $this->location);
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
	 * Get user agent.
	 *
	 * @return string
	 */
	public function getUserAgent(){
		return $this->user_agent;
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
	 * Get Script URL.
	 *
	 * @return string
	 */
	public function getScriptUrl(){
		return $this->script_url;
	}

	/**
	 * Get status message.
	 *
	 * Return DOMElement containing the current status message.
	 *
	 * @return DOMElement
	 */
	public function getStatusMessage(){

		if(!defined('HTTP_STATUS_MESSAGE_FILE')){
			/**
			 * HTTP status message file.
			 *
			 * @var string filename
			 * @internal
			 */
			define('HTTP_STATUS_MESSAGE_FILE', 'share/messages.xml');
		}

		$session = Locator::get('Corelib\Base\Session\Handler');
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
			} catch (Exception $e){
				echo $e;
				exit;
			}
		}
		return false;
	}

	public function prepare(){
		$session = Locator::get('Corelib\Base\Session\Handler');
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
			header('Cache-Control: private, max-age='.($this->expires - time()).', must-revalidate');
			header('Pragma:');
		}

		if(is_null($this->location)){
			if(Locator::isLoaded('Corelib\Base\ErrorHandler')){
				if(Locator::get('Corelib\Base\ErrorHandler')->hasErrors()){
					header('HTTP/1.1 '.$this->status_code);
				}
			}

			header('Content-Location: '. $this->request_uri);

			$type = $this->content_type;
			if(!is_null($this->content_charset)){
				$type .= '; charset='.$this->content_charset;
			}
			header('Content-Type: '.$type);
			return true;
		} else {
			header('HTTP/1.1 303 See Other');
			header('Location: '.$this->location);
			return false;
		}
	}
}