<?php
namespace Corelib\Base;

class Logger {
	// Default Loglevel Log::CRITICAL | Log::ERROR | Log::Warning
	private static $level = 7;
	private static $engine = null;

	const CRITICAL = 1;
	const ERROR = 2;
	const WARNING = 4;
	const NOTICE = 8;
	const INFO = 16;
	const DEBUG = 32;

	const ALL = 2047;
	const NONE = 0;

	static function setEngine(Logger\Engine $engine){
		self::$engine = $engine;
	}

	static function setLevel($level){
		self::$level = $level;
	}

	static private function _write($message, $level){
		if(self::$level & $level){
			$backtrace = debug_backtrace();
			array_shift($backtrace);

			$timestamp = microtime(true);
			$file = null;
			$line = null;
			$function = null;

			if(sizeof($backtrace) > 0){
				$file = $backtrace[0]['file'];
				$line = $backtrace[0]['line'];

				if(isset($backtrace[1]['class'])){
					$function .= ' '.$backtrace[1]['class'].'::';
				}
				if(isset($backtrace[1]['function'])){
					$function .= $backtrace[1]['function'].'()';
				}
			}
			self::$engine->write($timestamp, $level, $message, $file, $line, $function);
		}
	}

	static public function critical($message){
		self::_write($message, self::CRITICAL);
	}
	static public function error($message){
		self::_write($message, self::ERROR);
	}
	static public function warning($message){
		self::_write($message, self::WARNING);
	}
	static public function notice($message){
		self::_write($message, self::NOTICE);
	}
	static public function info($message){
		self::_write($message, self::INFO);
	}
	static public function debug($message){
		self::_write($message, self::DEBUG);
	}
}
?>