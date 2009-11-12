<?php
class FileSizeConverter implements Converter {
	private $precision = null;
	
	public function __construct($precision = 2){
		$this->precision = $precision;
	}	
	
	public function convert($data){
		$suffix = 'b';
		if($data > 1024){
			$suffix = 'Kb';
			$data = $data / 1024;
		}
		if($data > 1024){
			$suffix = 'Mb';
			$data = $data / 1024;
		}
		if($data > 1024){
			$suffix = 'Gb';
			$data = $data / 1024;
		}
		if($data > 1024){
			$suffix = 'Tb';
			$data = $data / 1024;
		}
		return round($data, $this->precision).' '.$suffix;
	}
}
?>