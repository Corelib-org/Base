<?php


class i18nDateConverter implements Converter {
	private $format = null;
	private $timezone_offset = 0;
	
	public function __construct($format, $timezone_offset=0){
		$this->format = $format;
		$this->timezone_offset = $timezone_offset;
	}	
	
	public function convert($data){
		return strftime($this->format, ($data - $this->timezone_offset));
	}
}
?>