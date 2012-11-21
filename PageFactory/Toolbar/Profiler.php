<?php
namespace Corelib\Base\PageFactory\Toolbar;
use \Corelib\Base\Log\Logger;

class Profiler extends Item {


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
		Logger::setEngine(new ProfilerLogger($this));
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
		$entries = '<table style="width: 100%; border-spacing: 0;">';
		$entries .= '<thead style="font-weight:bold;"><tr><th style="text-align: right; padding-right: 15px;">Time</th><th colspan="2" style="text-align: right;">Execution time</th><th></th><th>File</th><th>Function</th><th>Message</th></tr></thead>';

		$last_timestamp = $this->start;


		while(list(,$val) = each($this->entries)){
			$entries .= '<tr style="background-color: '.$this->_getPriorityColor($val[1]).'">';
			$entries .= '<td class="date" style="white-space: nowrap; text-align: right; padding-right: 15px;">'.date('c', $val[0]).'</td>';
			$entries .= '<td class="number" style="white-space: nowrap; text-align: right;">'.number_format(round(($val[0] - $this->start) , 4) * 1000, 2).' ms</td>';
			$entries .= '<td class="number" style="white-space: nowrap; text-align: right;">&#160;(+'.number_format(round(($val[0] - $last_timestamp) , 4) * 1000, 2).' ms)</td>';
			$entries .= '<td style="padding-left: 15px; padding-right: 15px;">'.$this->_getPriority($val[1]).'</td>';
			$entries .= '<td style="white-space: nowrap; padding-right: 15px;">'.htmlspecialchars($val[3]).':'.htmlspecialchars($val[4]).'</td>';
			$entries .= '<td style="white-space: nowrap; padding-right: 15px;">'.htmlspecialchars($val[5]).'</td>';
			$entries .= '<td style="padding-top: 10px; padding-bottom: 10px;">'.htmlspecialchars($val[2]).'</td>';
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
			return '#CC1111';
		} else if($level & Logger::ERROR){
			return '#FFBBBB';
		} else if($level & Logger::WARNING){
			return '#FFAA00';
		} else if($level & Logger::NOTICE){
			return '#AAAAFF';
		} else if($level & Logger::INFO){
			return 'inherit';
		} else if($level & Logger::DEBUG){
			return '#AAAAFF';
		}
	}

	private function _getPriorityTextColor($level){
		if($level & Logger::CRITICAL){
			return '#CC1111';
		} else if($level & Logger::ERROR){
			return '#FFBBBB';
		} else if($level & Logger::WARNING){
			return '#FFAA00';
		} else if($level & Logger::NOTICE){
			return '#AAAAFF';
		} else if($level & Logger::INFO){
			return 'inherit';
		} else if($level & Logger::DEBUG){
			return '#AAAAFF';
		}
	}
}