<?php
namespace Corelib\Base\Log\Engines;

class HTTPFile extends File {
	private $fp = null;

	protected function _createLogLine($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$prefix = '';

		if(defined('SESSION_COOKIE_NAME') && isset($_COOKIE[SESSION_COOKIE_NAME])){
			$prefix .= $_COOKIE[SESSION_COOKIE_NAME].' ';
		}
		$prefix .= $_SERVER['REQUEST_METHOD'].' "'.$_SERVER['REQUEST_URI'].'" ';

		return parent::_createLogLine($timestamp, $level, $prefix.$message, $file, $line, $function);
	}
}
?>