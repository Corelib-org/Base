<?php
/*
class LoggerEngineStdout extends LoggerEngineFile {
	private $stdout = null;
	private $stderr = null;

	public function __construct(){
		$this->stdout = fopen('php://stdout', 'a');
		$this->stderr = fopen('php://stderr', 'a');
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$message = $this->_createLogLine($timestamp, $level, $message, $file, $line, $function);
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
}
*/
?>