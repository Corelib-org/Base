<?php
class i18nDateToolsYearList implements Output {
	private $start = null;
	private $end = null;
	
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
		$years = $xml->createElement('years');
		if($this->end >= $this->start){
			for ($i = $this->start; $i <= $this->end; $i++){
				$years->appendChild($xml->createElement('year', $i));
			}
		}
		return $years;
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
?>