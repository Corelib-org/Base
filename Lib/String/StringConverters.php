<?php
class StringConverterNl2br implements Converter {
	public function convert($data) {
		return nl2br($data);
	}
}

class StringConverterHTMLEntities implements Converter {
	/**
	 * @param Converter $converter If converter is set, this will be applies after
	 *                             data has been ran through htmlentities.
	 */
	public function __construct(Converter $converter = null, $qoutestyle = null, $charset = 'UTF-8') {
		$this->converter = $converter;
		$this->charset = $charset;
		$this->quotestyle = $qoutestyle;
	}
	
	public function convert($data) {
		$data = htmlentities($data,$this->quotestyle,$this->charset);
		if($this->converter) {
			$data = $this->converter->convert($data);
		}

		// double the htmlentities xsl parser.
		return htmlentities($data,$this->quotestyle,$this->charset);
	}
}

class StringConverterSubstring implements Converter {
	private $length = null;
	private $cutsymbol = '...';
	private $smart = false;
	private $wordsafe = false;
	

	public function __construct($length, $smart=false, $wordsafe=true, $cutsymbol='...'){
		$this->length = $length;
		$this->cutsymbol = $cutsymbol;
		$this->smart = $smart;
		$this->wordsafe = $wordsafe;
	}
	
	public function convert($data){
		if(strlen($data) > $this->length){
			if($this->smart){
				return $this->_smartSubstring($data);
			} else {
				return $this->_substr($data, $this->length).$this->cutsymbol;
			}
		} else {
			return $data;
		}
	}
	
	public function _smartSubstring($data){
		$cut = strlen($data) - $this->length;
		$cut_left = floor($cut / 2); 
		$cut_right = ceil($cut / 2); 
		$split = floor(strlen($data) / 2);
		
		$left = substr($data, 0, $split);
		$left = substr($left, 0, strlen($left) - $cut_left);
		
		$right = substr($data, $split);
		$right = substr($right, $cut_right);

		return $left.$this->cutsymbol.$right;
	}	
	
	
	private function _substr($string, $length){
		if($this->wordsafe){
			while($string{$length} != ' '){
				$length++;
				if($length > strlen($string)){
					break;
				}
			}
		}
		return substr($string, 0, $length);
	}	
}
?>