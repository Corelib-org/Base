<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib default converters.
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
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Base.php 5066 2009-09-24 09:32:09Z wayland $)
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('LOOPBACK_STREAM_LOCALHOST')){
	/**
	 * Define loopback host address.
	 *
	 * @var string hostname or ip address
	 */
	define('LOOPBACK_STREAM_LOCALHOST', 'localhost');
}


//*****************************************************************//
//******************** LoopbackStream class ***********************//
//*****************************************************************//
/**
 * Loopback stream wrapper.
 *
 * The internal loopback stream allows you to do loopback requests
 * to the webserver running on the same host as the corelib site. using
 * the virtual hostname as host when making the request.
 * Usage example: file_get_contents('loopback://local.domain/local/path');
 *
 * @category corelib
 * @package Base
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 */
class LoopbackStream {


	//*****************************************************************//
	//*************** LoopbackStream class properties *****************//
	//*****************************************************************//
	/**
	 * @var string hostname
	 * @internal
	 */
	private $hostname = null;

	/**
	 * @var string request path.
	 * @internal
	 */
	private $path = null;

	/**
	 * @var string request query.
	 * @internal
	 */
	private $query = '';

	/**
	 * @var resource socket resource
	 * @internal
	 */
	private $socket = null;


	//*****************************************************************//
	//***************** LoopbackStream class methods ******************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $path
	 * @param string $mode
	 * @param string $options
	 * @param string $opened_path
	 * @return void
	 * @internal
	 */
	public function stream_open($path , $mode , $options , &$opened_path){
		if($this->socket = fsockopen(LOOPBACK_STREAM_LOCALHOST, 80)){
			$this->_parsePath($path);
			$request  = 'GET '.$this->path.$this->query.' HTTP/1.1'."\r\n";
			$request .= 'User-Agent: Corelib v'.CORELIB_BASE_VERSION."\r\n";
			$request .= 'Accept: text/html,application/xhtml+xml'."\r\n";
			$request .= 'Accept-Language: '.$_SERVER['HTTP_ACCEPT_LANGUAGE']."\r\n";
			$request .= 'Host: '.$this->hostname."\r\n";
			$request .= 'Cache-Control: max-age=0'."\r\n";
			$request .= 'Connection: close'."\r\n";
			if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
				$request .= 'Authorization: Basic '.base64_encode($_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'])."\r\n";
			}
			$request .= "\r\n";
			fwrite($this->socket, $request);

			while (!feof($this->socket)) {
				$data = trim(fgets($this->socket, 1024));
		    	if(empty($data)){
		    		return true;
		    	}
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 * Retrieve information about a file resource.
	 *
	 * @see http://dk.php.net/manual/en/function.stat.php
	 * @return integer
	 * @internal
	 */
	public function stream_stat(){
		return fstat($this->socket);
	}

	/**
	 * Read from stream.
	 *
	 * @param integer $count How many bytes of data from the current position should be returned.
	 * @return mixed string if there are less than $count bytes available,
	 *         return as many as are available. If no more data is available,
	 *         return either false or an empty string.
	 * @internal
	 */
	public function stream_read($count){
		return fgets($this->socket, $count);
	}

	/**
	 * Tests for end-of-file on a file pointer.
	 *
	 * @return boolean true if end-of-file, else return false
	 * @internal
	 */
	public function stream_eof(){
		return feof($this->socket);
	}

	/**
	 * Flushes the output.
	 *
	 * @return true on success, else return false
	 * @internal
	 */
	 public function stream_flush(){
	 	return fflush($this->socket);
	 }

	/**
	 * Close resource.
	 *
	 * @return void
	 * @internal
	 */
	public function stream_close(){
		fclose($this->socket);
	}

	/**
	 * Parse url.
	 *
	 * @param string $path
	 * @return void
	 * @internal
	 */
	private function _parsePath($path){
		$url = parse_url($path);
		if(!isset($url['path'])){
			$this->path = '/';
		} else {
			$this->path = $url['path'];
		}
		if(isset($url['query'])){
			$this->query = '?'.$url['query'];
		}
		$this->hostname = $url['host'];
	}

	/**
	 * Destruct the object.
	 *
	 * @return void
	 * @internal
	 */
	public function __destruct(){
		if(is_resource($this->socket)){
			$this->stream_close();
		}
	}

	/**
	 * Method fallback.
	 *
	 * @param string $name method name
	 * @param array $arguments
	 * @return boolean false
	 */
    public function __call($name, $arguments){
    	trigger_error(__CLASS__.'::'.$name.' is not avaialbe', E_USER_ERROR);
		return false;
    }
}

stream_wrapper_register('loopback', 'LoopbackStream');
?>