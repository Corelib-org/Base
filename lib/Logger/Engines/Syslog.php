<?php
class Syslog extends LoggerEngine {
	private $syslog = null;

	public function __construct($ident='corelib', $options=11, $facility=LOG_USER){
		openlog($ident, $options, $facility);
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		syslog($this->_getPriority($level), $this->_createLogLine($timestamp, $level, $message, $file, $line, $function));
	}

	public function __destruct(){
		closelog();
	}

	private function _getPriority($level){
		if($level & Logger::CRITICAL){
			return LOG_CRIT;
		} else if($level & Logger::ERROR){
			return LOG_ERR;
		} else if($level & Logger::WARNING){
			return LOG_WARNING;
		} else if($level & Logger::NOTICE){
			return LOG_NOTICE;
		} else if($level & Logger::INFO){
			return LOG_INFO;
		} else if($level & Logger::DEBUG){
			return LOG_DEBUG;
		}
	}
}
?>