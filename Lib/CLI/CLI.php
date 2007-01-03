<?php
define('CORELIB_CLI_VERSION', '1.0.0');

class CLI implements Singleton {
	
	private static $instance = null;
	private $stderr = null;
	private $stdout = null;

	private function __construct(){
		$this->stdout = @fopen('php://stdout', 'w');
		$this->stderr = @fopen('php://stderr', 'w');
		$this->debugCopyright('Corelib CLI v'.CORELIB_CLI_VERSION.' Copyright '.CORELIB_COPYRIGHT_YEAR.' '.CORELIB_COPYRIGHT);
	}
	
	/**
	 *	@return CLI
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new CLI();
		}
		return self::$instance;	
	}
	
	public function debugVerbose($string){
		$this->debug('DEBUG: '.$string);
	}
	public function debugInfo($string){
		$this->debug('INFO: '.$string);
	}
	public function debugStatus($string){
		$this->debug('STATUS: '.$string);
	}
	public function debugNotice($string){
		$this->debug('NOTICE: '.$string);
	}
	public function debugError($string){
		$this->debug('ERROR: '.$string, $this->stderr);
	}
	public function debugFatal($string){
		$this->debug('FATAL: '.$string, $this->stderr);
	}
	public function debugCopyright($string){
		$this->debug($string, null, false);
	}
	
	private function debug($string, $target=null, $date=true){
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