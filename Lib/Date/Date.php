<?php
class Date {
	private $timestamp = null;
	private $timezone = null;

	public function __construct($timestamp = null, $timezone=null){
		try {
			if(!is_null($timestamp)){
				if(StrictTypes::isInteger($timestamp)){
					$this->timestamp = $timestamp;
				}
			} else {
				$this->timestamp = time();
			}
			if(!is_null($timezone)){
				if(StrictTypes::isString($timezone)){
					$this->timezone = $timezone;
				}
			} else {
				if(function_exists('date_default_timezone_get')){
					$this->timezone = date_default_timezone_get();
				}
			}
		} catch (BaseException $e){
			echo $e;
		}
	}

	public function convert($format){
		return date($format, $this->timestamp);
	}

	public function getTimestamp(){
		return $this->timestamp;
	}
}

abstract class DateDisplay implements Converter, Output {
	/**
	 * @var Date
	 */
	protected $date = null;

	public function __clone(){
		$this->date = null;
	}

	/**
	 * @deprecated use DateDisplay::getDisplayObject() and DateDisplay::setDate()
	 */
	public function setTimestamp($timestamp, $clone=false){
		trigger_error('deprecated use DateDisplay::getDisplayObject() and DateDisplay::setDate()', E_USER_NOTICE);
		if(!$clone){
			return $this->getDisplayObject($timestamp);
		} else {
			try {
				StrictTypes::isInteger($timestamp);
			} catch (BaseException $e){
				echo $e;
				return false;
			}
			$this>setDate(new Date($timestamp));
			return true;
		}
	}

	/**
	 * @param integer $timestamp
	 * @return DateDisplay
	 */
	public function getDisplayObject($timestamp){
		try {
			StrictTypes::isInteger($timestamp);
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		$clone = clone $this;
		$clone->setDate(new Date($timestamp));
		return $clone;
	}
	public function setDate(Date $date){
		$this->date = $date;
	}
	public function getDate(){
		return $this->date;
	}

	public function convert($data){
		return $this->getDisplayObject($data);
	}
	public function &getArray() {}
}

class DateDisplayMonthDateYear extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('month', $this->date->convert('M')));
		$date->appendChild($xml->createElement('month_num', $this->date->convert('n')));
		$date->appendChild($xml->createElement('date', $this->date->convert('d')));
		$date->appendChild($xml->createElement('year', $this->date->convert('Y')));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['month'] = $this->date->convert('M');
		$date['date'] = $this->date->convert('d');
		$date['year'] = $this->date->convert('Y');
		return $date;
	}
}
class DateDisplayMonthYear extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('month', $this->date->convert('M')));
		$date->appendChild($xml->createElement('month_num', $this->date->convert('n')));
		$date->appendChild($xml->createElement('year', $this->date->convert('Y')));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['month'] = $this->date->convert('M');
		$date['year'] = $this->date->convert('Y');
		return $date;
	}
}
class DateDisplayAgeYear extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('age', $this->date->getAge()));
		$date->appendChild($xml->createElement('year', $this->date->convert('Y')));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['age'] = $this->date->getAge();
		$date['year'] = $this->date->convert('Y');
		return $date;
	}
}
class DateDisplayAgeMonthDateYear extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('age', $this->date->getAge()));
		$date->appendChild($xml->createElement('month', $this->date->convert('n')));
		$date->appendChild($xml->createElement('date', $this->date->convert('j')));
		$date->appendChild($xml->createElement('year', $this->date->convert('Y')));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['age'] = $this->date->getAge();
		$date['month'] = $this->date->convert('n');
		$date['date'] = $this->date->convert('j');
		$date['year'] = $this->date->convert('Y');
		return $date;
	}
}
class DateDisplayRFC822 extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('date', $this->date->convert(DATE_RFC822)));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['date'] = $this->date->convert(DATE_RFC822);
		return $date;
	}
}
class DateDisplayISO8601 extends DateDisplay {
	public function getXML(DOMDocument $xml){
		$date = $xml->createElement('datedisplay');
		$date->setAttribute('type',__CLASS__);
		$date->appendChild($xml->createElement('date', $this->date->convert(DATE_ISO8601)));
		return $date;
	}
	public function &getArray() {
		$date = array();
		$date['date'] = $this->date->convert(DATE_ISO8601);
		return $date;
	}
}
?>