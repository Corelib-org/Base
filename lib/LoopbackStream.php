<?php
if(!defined('LOOPBACK_STREAM_LOCALHOST')){
	define('LOOPBACK_STREAM_LOCALHOST', 'localhost');
}

class LoopbackStream {
	private $hostname = null;
	private $path = null;
	private $query = '';

	private $socket = null;

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

	public function stream_stat(){
		return fstat($this->socket);
	}

	public function stream_read($count){
		return fgets($this->socket, $count);
	}

	public function stream_eof(){
		return feof($this->socket);
	}

	 public function stream_flush(){
	 	return fflush($this->socket);
	 }

	public function stream_close(){
		fclose($this->socket);
	}

	/*
    public function stream_lock($operation);
    public function stream_seek($offset , $whence = SEEK_SET);
    public function stream_set_option($option , $arg1 , $arg2);
    public function stream_tell();
    public function stream_write($data);
    public function url_stat($path , $flags);
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

	public function __destruct(){
		if(is_resource($this->socket)){
			$this->stream_close();
		}
	}

    public function __call($name, $arguments){
    	trigger_error(__CLASS__.'::'.$name.' is not avaialbe', E_USER_ERROR);
		return false;
    }
}


stream_wrapper_register('loopback', 'LoopbackStream');
?>