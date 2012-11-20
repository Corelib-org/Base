<?php
namespace Corelib\Base\PageFactory\Toolbar;

class ProfilerLogger extends \LoggerEngine {

	private $profiler;

	public function __construct(Profiler $profiler){
		$this->profiler = $profiler;
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$this->profiler->addEntry($timestamp, $level, $message, $file, $line, $function);
	}
}
