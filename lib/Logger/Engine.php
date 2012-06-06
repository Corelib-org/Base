<?php
namespace Corelib\Base\Logger;

abstract class Engine {
	abstract public function write($timestamp, $level, $message, $file=null, $line=null, $function=null);

	protected function _createLogLine($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$prefix = '';
		if(!is_null($file)){
			$prefix .= $file;
		}
		if(!is_null($line)){
			$prefix .= ':'.$line.' ';
		}
		if(!is_null($function)){
			$prefix .= $function.': ';
		}
		return $prefix.$message;
	}
}
?>