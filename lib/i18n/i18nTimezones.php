<?php
class i18nTimezones implements Output {
	private $timezones = null;
	
	public function __construct($timezones=null){
		 $this->timezones = $timezones;
	}
	
	public function getXML(DOMDocument $xml){
		if(is_null($this->timezones)){
			$this->timezones = timezone_identifiers_list();
		}
		$timezones = $xml->createElement('timezones');
		foreach($this->timezones as $key => $val) {
			if(is_array($val)){
				$key = $val[0];
				$val = $val[1];
			}
			$timezone = $timezones->appendChild($xml->createElement('timezone', $val));
			if(!is_numeric($key)){
				$timezone->setAttribute('name', $key);
			} else {
				$timezone->setAttribute('name', $val);
			}
		}
		reset($this->timezones);
		return $timezones;
	}
	
	public function &getArray(){
		
	}
}
?>