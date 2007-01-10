<?php
define('CORELIB_DEBUGGER_VERSION', '1.0.0');

class Debugger implements Singleton {

	private static $instance = null;
	private $stderr = null;
	private $stdout = null;

	private function __construct(){
		$this->stdout = @fopen('php://stdout', 'w');
		$this->stderr = @fopen('php://stderr', 'w');
		$this->copyright('Corelib Debugger v'.CORELIB_DEBUGGER_VERSION.' Copyright '.CORELIB_COPYRIGHT_YEAR.' '.CORELIB_COPYRIGHT);
	}

	/**
	 *	@return Debugger
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Debugger();
		}
		return self::$instance;
	}

	public function debug($string){
		$this->_sendMessage('DEBUG: '.$string);
	}
	public function status($string){
		$this->_sendMessage('STATUS: '.$string);
	}
	public function notice($string){
		$this->_sendMessage('NOTICE: '.$string);
	}
	public function error($string){
		$this->_sendMessage('ERROR: '.$string, $this->stderr);
	}
	public function copyright($string){
		$this->_sendMessage($string, null, false);
	}

	private function _sendMessage($string, $target=null, $date=true){
		if(php_sapi_name() == 'cli'){
			if($date){
				$string = '['.date('r').'] '.$string;
			}
			$string .= "\n";
			if(is_null($target)){
				$target = $this->stdout;
			}
			if(!is_resource($target)){
				echo $string;
			} else {
				fwrite($target, $string, strlen($string));
			}
		}
	}
}
?>