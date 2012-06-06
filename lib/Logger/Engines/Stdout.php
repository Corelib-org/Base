<?php
namespace Corelib\Base\Logger\Engine;

use Corelib\Base\Logger\Engine;
use Corelib\Base\Logger;

class Stdout extends Engine {
	private $stdout = null;
	private $stderr = null;

	public function __construct(){
		$this->stdout = fopen('php://stdout', 'a');
		$this->stderr = fopen('php://stderr', 'a');
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$message = date('c').' '.str_pad($this->_getPriority($level), 9).' '.$this->_createLogLine($timestamp, $level, $message, $file, $line, $function)."\n";
		if($level & (Logger::CRITICAL ^ Logger::ERROR)){
			fwrite($this->stderr, $message, strlen($message));
		} else {
			fwrite($this->stdout, $message, strlen($message));
		}
	}

	public function __destruct(){
		fclose($this->stdout);
		fclose($this->stderr);
	}

	private function _getPriority($level){
		if($level & Logger::CRITICAL){
			return 'critical';
		} else if($level & Logger::ERROR){
			return 'error';
		} else if($level & Logger::WARNING){
			return 'warning';
		} else if($level & Logger::NOTICE){
			return 'notice';
		} else if($level & Logger::INFO){
			return 'info';
		} else if($level & Logger::DEBUG){
			return 'debug';
		}
	}
}
?>