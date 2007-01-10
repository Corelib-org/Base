<?php
define('CORELIB_CLI_VERSION', '1.0.0');

class CLI {
	public static function getArgumentByFlag($flag){
		global $argv;
		try {
			StrictTypes::isString($flag);
		} catch (BaseException $e){
			echo $e;
		}
		if(isset($argv) && is_array($argv)){
			while(list($key, $val) = each($argv)){
				if($val == $flag){
					if(isset($argv[($key + 1)]) && !preg_match('/^-/', $argv[($key + 1)])){
						reset($argv);
						return $argv[($key + 1)];
					} else {
						reset($argv);
						return true;
					}
				}
			}
			reset($argv);
		}
		return false;
	}
}
?>