<?php

class PageFactoryDeveloperToolbarProfilerLogger extends LoggerEngine {

	private $profiler;

	public function __construct(PageFactoryDeveloperToolbarProfiler $profiler){
		$this->profiler = $profiler;
	}

	public function write($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$this->profiler->addEntry($timestamp, $level, $message, $file, $line, $function);
	}
}


class PageFactoryDeveloperToolbarProfiler extends PageFactoryDeveloperToolbarItem {


	//************************************************************************************//
	//*************** PageFactoryDeveloperToolbarProfiler class properties ***************//
	//************************************************************************************//
	/**
	 * @var float start microtime
	 */
	private $start = null;

	/**
	 * @var CacheManager
	 * @internal
	 */
	private $entries = array();


	//************************************************************************************//
	//***************** PageFactoryDeveloperToolbarProfiler class methods ****************//
	//************************************************************************************//
	/**
	 * Constructor.
	 *
	 * Assigns current {@link http://www.php.net/manual/en/function.microtime.php} to
	 * {@link PageFactoryDeveloperToolbarItemExectutionTimeCalculator::$start}
	 *
	 * @uses PageFactoryDeveloperToolbarProfiler::$start
	 * @return void
	 */
	public function __construct(){
		$this->start = microtime(true);
		Logger::setEngine(new PageFactoryDeveloperToolbarProfilerLogger($this));
	}


	public function addEntry($timestamp, $level, $message, $file=null, $line=null, $function=null){
		$this->entries[] = func_get_args();
	}


	/**
	 * Get toolbar item.
	 *
	 * Return html string containing a execution time icon and the actual execution time.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 */
	public function getToolbarItem(){
		return '<img src="corelib/resource/manager/images/icons/toolbar/parsetime.png" alt="parsetime" title="Page execution time"/> '.(round((microtime(true) - $this->start) , 4) * 1000).' ms.';
	}

	/**
	 * Get toolbar item content.
	 *
	 * @see PageFactoryDeveloperToolbarItem::etContent()
	 * @return string
	 * @internal
	 */
	public function getContent(){
		$entries = '<table style="width: 100%;">';
		$entries .= '<thead style="font-weight:bold;"><tr><td>Time</td><td>Execution time</td><td>Since last log</td><td>Severity</td><td>File</td><td>Function</td><td>Message</td></tr></thead>';

		$last_timestamp = $this->start;


		while(list(,$val) = each($this->entries)){
			$entries .= '<tr style="background-color: '.$this->_getPriorityColor($val[1]).'">';
			$entries .= '<td class="date" style="width: 150px; text-align: right;">'.date('c', $val[0]).'</td>';
			$entries .= '<td class="number" style="width: 100px; text-align: right;">'.number_format(round(($val[0] - $this->start) , 4) * 1000, 2).' ms&#160;	</td>';
			$entries .= '<td class="number" style="width: 100px; text-align: right;">+'.number_format(round(($val[0] - $last_timestamp) , 4) * 1000, 2).' ms&#160;	</td>';
			$entries .= '<td>'.$this->_getPriority($val[1]).'</td>';
			$entries .= '<td>'.$val[3].':'.$val[4].'</td>';
			$entries .= '<td>'.$val[5].'</td>';
			$entries .= '<td>'.$val[2].'</td>';
			$entries .= '</tr>';
			$last_timestamp = $val[0];
		}
		$entries .= '</table>';
		return $entries;
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

	private function _getPriorityColor($level){
		if($level & Logger::CRITICAL){
			return '#AAAAFF';
		} else if($level & Logger::ERROR){
			return '#AAAAFF';
		} else if($level & Logger::WARNING){
			return '#FFAA00';
		} else if($level & Logger::NOTICE){
			return '#AAAAFF';
		} else if($level & Logger::INFO){
			return '#EFEFFF';
		} else if($level & Logger::DEBUG){
			return '#AAAAFF';
		}
	}
}