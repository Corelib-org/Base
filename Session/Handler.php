<?php
namespace Corelib\Base\Session;

use Corelib\Base\ServiceLocator\Service, Corelib\Base\ServiceLocator\Autoloadable, Corelib\Base\PageFactory\Output;


/**
 * Session handler.
 *
 * @category corelib
 * @package Base
 * @subpackage SessionHandler
 */
class Handler implements Service,Autoloadable,Output {

	/**
	 * Engine reference.
	 *
	 * @var SessionHandlerEngine
	 * @internal
	 */
	private $engine = null;

	/**
	 * Cookie domain.
	 *
	 * @var string
	 * @internal
	 */
	private $domain = null;

	/**
	 * Cookie lifetime.
	 *
	 * @var integer seconds
	 * @internal
	 */
	private $ttl = 0;

	/**
	 * Cookie path.
	 *
	 * @var string
	 * @internal
	 */
	private $path = '/';

	/**
	 * Secure cookie.
	 *
	 * @var boolean
	 * @internal
	 */
	private $secure = false;

	/**
	 * List of output converters
	 *
	 * @var array
	 * @internal
	 */
	private $converters = array();

	private $is_init = false;

	//*****************************************************************//
	//***************** SessionHandler class methods ******************//
	//*****************************************************************//
	/**
	 * Session handler constructor.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(Engine $engine=null, $ttl=0, $secure=false, $domain=null, $path=null, $name='SSID'){
		$this->ttl = $ttl;
		$this->secure = $secure;
		$this->domain = $domain;
		$this->path = $path;
		$this->name = $name;

		if(is_null($engine)){
			$this->engine = new Engines\PHP();
		}
	}


	/**
	 * Initiate session.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function init(){
		if(!$this->is_init){
			$this->is_init = $this->engine->init($this->ttl, $this->path, $this->domain, $this->secure, $this->name);
		}
		return $this->is_init;
	}

	/**
	 * Set session variable.
	 *
	 * @param string $name
	 * @param mixed $content
	 * @return void
	 */
	public function set($name, $content){
		assert('is_string($name)');
		if($this->init()){
			while($this->isLocked($name)){
				usleep(1);
			}
			$this->lock($name);
			$data = $this->engine->set($name, $content);
			$this->unlock($name);
		} else {
			return false;
		}
	}

	/**
	 * Get session variable.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name){
		assert('is_string($name)');
		if($this->init()){
			return $this->engine->get($name);
		} else {
			return false;
		}
	}

	/**
	 * Check if session variable exists.
	 *
	 * @param string $name
	 * @return boolean true if it exists else return false
	 */
	public function check($name){
		assert('is_string($name)');
		if($this->init()){
			return $this->engine->check($name);
		} else {
			return false;
		}
	}

	/**
	 * Remove session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function remove($name){
		assert('is_string($name)');
		if($this->init()){
			while($this->isLocked($name)){
				usleep(1);
			}
			$this->lock($name);
			$status = $this->engine->remove($name);
			return $status;
		} else {
			return false;
		}
	}

	/**
	 * Lock session variable.
	 *
	 * This method does not work with all session handling
	 * engines. please refer to you engine for information
	 * about support.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function lock($name){
		assert('is_string($name)');

		if($this->init()){
			while($this->isLocked($name)){
				usleep(1);
			}
			return $this->engine->lock($name);
		} else {
			return false;
		}
	}

	/**
	 * Check to see if variable is locked.
	 *
	 * @param string $name
	 * @return boolean true if locked, else return false
	 */
	public function isLocked($name){
		assert('is_string($name)');
		if($this->init()){
			return $this->engine->isLocked($name);
		} else {
			return false;
		}
	}

	/**
	 * Unlock session variable.
	 *
	 * @param string $name
	 * @return boolean true on success, else return false
	 */
	public function unlock($name){
		assert('is_string($name)');
		if($this->init()){
			return $this->engine->unlock($name);
		} else {
			return false;
		}

	}

	/**
	 * Destroy session.
	 *
	 * @return boolean true in success, else return false
	 */
	public function destroy(){
		return $this->engine->destroy();
	}

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function getID(){
		if($this->init()){
			return $this->engine->getID();
		} else {
			return false;
		}
	}


	/**
	 * Set output converter for session variable.
	 *
	 * @param string $name
	 * @param Converter $converter
	 * @return boolean true in success, else return false
	 */
	public function setConverter($name, Converter $converter){
		$this->converters[$name] = $converter;
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	public function getXML(\DOMDocument $xml){
		$session = $xml->createElement('session');
		$session->setAttribute('id', $this->getID());
		$this->_getXMLArray($session, $this->engine->getVariables());
		return $session;
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	/*
	public function getXML(DOMDocument $xml){
		$session = $xml->createElement('variables');
		$this->_getXMLArray($session, $_SESSION);
		return $session;
	}
	*/

	/**
	 * Convert session array to dom tree.
	 *
	 * @param DOMElement $parent
	 * @param array $array
	 * @return void
	 * @internal
	 */
	private function _getXMLArray(\DOMElement $parent, array $array){
		while(list($key, $val) = each($array)){
			if(preg_match('/(^[0-9])|(,)/', $key)){
				$key = 'item';
			}
			if(is_array($val)){
				$key = $parent->appendChild($parent->ownerDocument->createElement($key));
				$this->_getXMLArray($key, $val);
			} else {
				if(isset($this->converters[$key])){
					$val = $this->converters[$key]->convert($val);
				}
				$parent->appendChild($parent->ownerDocument->createElement($key, htmlspecialchars($val)));
			}
		}
	}
}
?>