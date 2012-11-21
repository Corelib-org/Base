<?php
/**
 * Usage:
 *



Logger::setEngine(new Stdout());
Logger::setLevel(Logger::ALL); // Default log level is: Log::CRITICAL | Log::ERROR | Log::Warning, log level set exactly as error_reporting()
Logger::error('Test Error');
Logger::warning('Test Warning');
Logger::critical('Test Critical');
Logger::info('Test info');
Logger::debug('Test debug');
 */
/*
class Logger {
	// Default Loglevel Logger::CRITICAL | Logger::ERROR | Logger::WARNING | Logger::Notice
	private static $level = 15;
	private static $engine = null;

	const CRITICAL = 1;
	const ERROR = 2;
	const WARNING = 4;
	const NOTICE = 8;
	const INFO = 16;
	const DEBUG = 32;

	const ALL = 2047;
	const NONE = 0;

	static public function setEngine(LoggerEngine $engine=null){
		self::$engine = $engine;
	}

	static public function setLevel($level){
		self::$level = $level;
	}

	static private function _write($message, $level, $backtrace_stack=0){
		if(self::$level & $level && !is_null(self::$engine)){

			$backtrace = debug_backtrace(false);
			array_shift($backtrace);

			$timestamp = microtime(true);
			$file = null;
			$line = null;
			$function = null;


			if(sizeof($backtrace) > $backtrace_stack){
				$file = $backtrace[$backtrace_stack]['file'];
				$line = trim($backtrace[$backtrace_stack]['line']);

				$backtrace_stack++;

				if(isset($backtrace[$backtrace_stack]['class'])){
					$function .= $backtrace[$backtrace_stack]['class'].'::';
				}
				if(isset($backtrace[$backtrace_stack]['function'])){
					$function .= $backtrace[$backtrace_stack]['function'].'()';
				}
			}
			self::$engine->write($timestamp, $level, $message, $file, $line, $function);
		}
	}

	static public function critical($message, $backtrace_stack=0){
		self::_write($message, self::CRITICAL, $backtrace_stack);
	}
	static public function error($message, $backtrace_stack=0){
		self::_write($message, self::ERROR, $backtrace_stack);
	}
	static public function warning($message, $backtrace_stack=0){
		self::_write($message, self::WARNING, $backtrace_stack);
	}
	static public function notice($message, $backtrace_stack=0){
		self::_write($message, self::NOTICE, $backtrace_stack);
	}
	static public function info($message, $backtrace_stack=0){
		self::_write($message, self::INFO, $backtrace_stack);
	}
	static public function debug($message, $backtrace_stack=0){
		self::_write($message, self::DEBUG, $backtrace_stack);
	}
}
*/
?>