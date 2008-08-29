<?php
abstract class i18nDateTools {
	protected function _parseOffsetString($string) {
		switch($string[0]){
			case '-':
				return substr($string, 1) * -1;
				break;
			case '+':
				return substr($string, 1) * 1;
				break;
			default:
				return (int) $string;
		}
	}
}

class i18nDateToolsYearList implements Output {
	private $start = null;
	private $end = null;
	private $date = null;
	
		
	public function __construct($start=null, $end=null){
		if(is_null($start)){
			$this->start = 1970;
		} else {
			$this->start = $this->_parseYearOffsetString($start);
		}
		if(is_null($end)){
			$this->end = date('Y');
		} else {
			$this->end = $this->_parseYearOffsetString($end);
		}
	}
	
	public function getXML(DOMDocument $xml){
		$current_year = null;
		
		if($this->date) {
			$current_year = date('Y',$this->date);
		}
		
		$years = $xml->createElement('years');
		if($this->end >= $this->start){
			for ($i = $this->start; $i <= $this->end; $i++){
				$year = $years->appendChild($xml->createElement('year', $i));
				$year->setAttribute('numeric', $i);
				if($current_year == $i){
					$year->setAttribute('selected','true');
				}				
			}
		}
		return $years;
	}

	public function setTimestamp($date) {
		$this->date = $date;
	}	
	
	public function &getArray(){
		
	}

	/**
	 * @param mixed $string
	 * @return int 
	 */
	private function _parseYearOffsetString($year){
		if(is_string($year)){
			switch($year[0]){
				case '-':
					return date('Y') - (int) substr($year, 1);
					break;
				case '+':
					return date('Y') + (int) substr($year, 1);
					break;
				default:
					return (int) $year;
			}
		} else if($year < 1000){
			return date('Y') + $year;
		} else {
			return $year;
		}
	}
}

/**
 * Generates i18n xml representation of months names.
 * 
 * xml output:
 * <months>
 *     <month numeric="1">january</month>
 *     <month numeric="2">february</month>
 *     ...
 * </days>
 */
class i18nDateToolsMonthList implements Output {
	private $start = null;
	private $end = null;
	private $date = null;
	
	
	/**
	 * Constructor
	 * Both range start and range end values can be prepended with either a minus or a
	 * plus character to walk backward or forwards from current month
	 *
	 * @param mixed $start (optional) Range start
	 * @param mixed $end (optional) Range end
	 */
	public function __construct($start=null, $end=null){
		if(is_null($start) || ($start == 0)){
			$this->start = 1;
		} else {
			$this->start = $this->_parseOffsetString($start);
		}
		if(is_null($end) || ($end == 0)){
			$this->end = 12;
		} else {
			$this->end = $this->_parseOffsetString($end);
		}
	}
	
	public function setTimestamp($date) {
		$this->date = $date;
	}	
	
	public function getXML(DOMDocument $xml){
		$current_month = null;
		
		if($this->date) {
			$current_month = date('n',$this->date);
		}
				
		$months = $xml->createElement('months');
		if($this->end >= $this->start){
			for ($i = $this->start; $i <= $this->end; $i++){
				$name = strftime('%B',mktime(0,0,0,$i,1,1));
				$month = $months->appendChild($xml->createElement('month',$name));
				$month->setAttribute('numeric',$i);
				if($current_month == $i){
					$month->setAttribute('selected','true');
				}
			}
		}
		return $months;
	}
	
	public function &getArray(){
		
	}	
}

/**
 * Generates i18n xml representation of days in month.
 * 
 * xml output:
 * <days>
 *     ...
 *     <day numeric="5">friday</day>
 *     <day numeric="6">saturday</day>
 *     ...
 * </days>
 */
class i18nDateToolsDayList extends i18nDateTools implements Output {
	private $start = null;
	private $end = null;
	private $month = null;
	private $year = null;
	private $date = null;
	
	/**
	 * Constructur
	 *
	 * @param mixed $start Range start
	 * @param mixed $end Range start
	 * @param mixed $month Month offset
	 * @param mixed $year Year offset
	 */
	public function __construct($start=null ,$end=null , $month=null, $year=null) {
		if(is_null($start)){
			$this->start = 1;
		} else {
			$this->start = date('j') + $this->_parseOffsetString($start);
		}
		if(is_null($end)){
			$this->end = date('t');
		} else {
			$this->end = $this->start + $this->_parseOffsetString($end);
		}
		if(is_null($month) || ($month == 0)){
			$this->month = date('n');
		} else {
			$this->month = date('n')+$this->_parseOffsetString($month);
		}
		if(is_null($year) || ($year == 0)){
			$this->year = date('Y');
		} else {
			$this->year = date('Y')+$this->_parseOffsetString($year);
		}
	}
	
	public function setTimestamp($date) {
		$this->date = $date;
	}
	
	public function getXML(DOMDocument $xml) {
		$current_day = null;
		$current_month = null;
		$current_year = null;
		
		$days = $xml->createElement('days');
		if($this->date) {
			$current_day = date('j',$this->date);
			$current_month = date('n',$this->date);
			$current_year = date('Y',$this->date);
		}
		
		if($this->end >= $this->start){
			for ($i = $this->start; $i <= $this->end; $i++){
				$time = mktime(null,null,null,$this->month,$i,$this->year);
				$value = date('j',$time);
				$day = $days->appendChild($xml->createElement('day',strftime('%A',$time)));
				$day->setAttribute('numeric',$value);
				if($this->year == $current_year && $this->month == $current_month && $value == $current_day) {
					$day->setAttribute('current','true');
				}
				if($value == $current_day) {
					$day->setAttribute('selected','true');
				}
			}
		}
		return $days;
	}
	
	public function &getArray() {
		
	}
}

/**
 * Generates i18n xml representation of days, months and years.
 * 
 * xml output:
 * <datelist>
 *     <days>
 *     ...
 *     </days>
 *     <months>
 *     ...
 *     </months>
 *     <years>
 *     ...
 *     </years>
 * </datelist>
 */
class i18nDateToolsDateList implements Output {
	/**
	 * @var i18nDateToolsDayList $daylist
	 */
	private $daylist = null;

	/**
	 * @var i18nDateToolsMonthList $monthlist
	 */
	private $monthlist = null;

	/**
	 * @var i18nDateToolsYearList $yearlist
	 */
	private $yearlist = null;
	
	/**
	 * @var int
	 */
	private $date = null;
	
	/**
	 * Sets date to select in output
	 *
	 * @param string|int $date
	 */
	public function setTimestamp($date) {
		if(is_string($date)) {
			$this->date = strtotime($date);
			if(!$this->date) {
				$this->date = $date;
			}
		} else {
			$this->date = $date;
		}
	}
	
	public function setDayList(i18nDateToolsDayList $list) {
		$this->daylist = $list;
	}
	
	public function setMonthList(i18nDateToolsMonthList $list) {
		$this->monthlist = $list;
	}

	public function setYearList(i18nDateToolsYearList $list) {
		$this->yearlist = $list;
	}
	
	public function getXML(DOMDocument $xml) {
		$datelist = $xml->createElement('datelist');
		if($this->daylist) {
			if($this->date) {
				$this->daylist->setTimestamp($this->date);
			}
			$datelist->appendChild($this->daylist->getXML($xml));
		}
		if($this->monthlist) {
			if($this->date) {
				$this->monthlist->setTimestamp($this->date);
			}
			$datelist->appendChild($this->monthlist->getXML($xml));
		}
		if($this->yearlist) {
			if($this->date) {
				$this->yearlist->setTimestamp($this->date);
			}
			$datelist->appendChild($this->yearlist->getXML($xml));
		}
		return $datelist;
	}
	
	public function &getArray() {}
}
?>