<?php
interface Converter {
	public function convert($data);
}

class ConverterChain implements Converter {
	private $converters = array();
	
	public function addConveter(Converter $converter){
		$this->converters[] = $converter;
	}
	
	public function convert($data){
		foreach ($this->converters as $converter){
			$data = $converter->convert($data);
		}
		return $data;
	}
}


?>