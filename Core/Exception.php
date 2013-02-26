<?php
namespace Corelib\Base\Core;

use Serializable, Exception as NativeException;

/**
 * Base exception class.
 *
 * @category corelib
 * @package Base
 * @subpackage ErrorHandler
 */
class Exception extends NativeException implements Serializable {

	//*****************************************************************//
	//***************** BaseException class methods *******************//
	//*****************************************************************//
	/**
	 * Overwrite parent constructor.
	 *
	 * override original constructor in order to add support for
	 * both PHP < 5.3 where exception linking where unsupported and
	 * >= PHP 5.3 where exception linking is supported.
	 * Throwing this exception without any arguments, the exception will
	 * automatically fetch what ever is stored in error_get_last()
	 *
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 * @return void
	 */
	public function __construct($message = null, $code = 0, Exception $previous = null){
		if(is_null($message)){
			$message = error_get_last();
			$code = $message['type'];
			$message = strip_tags($message['message']);
		}

		if(version_compare(PHP_VERSION, '5.3') == -1){
			parent::__construct($message, $code);
		} else {
			parent::__construct($message, $code, $previous);
		}
	}

	public function __toString(){
		// trigger_error('Uncought exception: '.get_class($this).' - '.$this->getMessage(), E_USER_ERROR);
		return parent::__toString();
	}


	public function serialize(){
		return serialize(array($this->message, $this->code, $this->file, $this->line));
	}

	public function unserialize($serialized){
		list($this->message, $this->code, $this->file, $this->line) = unserialize($serialized);
	}

}