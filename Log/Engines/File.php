<?php
namespace Corelib\Base\Log\Engines;

class File extends Engine {
	private $fp = null;

	public function __construct($filename){
		$this->fp = fopen($filename, 'a');
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$message = $this->_createLogLine($timestamp, $level, $message, $file, $line, $function);
		fwrite($this->fp, $message, strlen($message));
		return true;
	}

	protected function _createLogLine($timestamp, $level, $message, $file=null, $line=null, $function=null){
		return trim(date('c').' '.str_pad($this->_getPriority($level), 9).' '.parent::_createLogLine($timestamp, $level, $message, $file, $line, $function))."\n";
	}

	public function __destruct(){
		fclose($this->fp);
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