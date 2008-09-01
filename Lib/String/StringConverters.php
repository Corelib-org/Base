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
	

	public function __construct($length, $smart=false, $cutsymbol='...'){
		$this->length = $length;
		$this->cutsymbol = $cutsymbol;
		$this->smart = $smart;
	}
	
	public function convert($data){
		if(strlen($data) > $this->length){
			if($this->smart){
				return $this->_smartSubstring($data);
			} else {
				return substr($data, 0, $this->length).$this->cutsymbol;
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
	
	/*
	smartSubstring(string, length, smart, cutsymbol){

    if(!cutsymbol){
        cutsymbol = '...';
    }
    len = string.length;
    if(len > length){
        if(smart){
            cutlen = len - length;
 
            cutleft = Math.floor(cutlen / 2);
            cutright = Math.ceil(cutlen / 2);
 
            leftsplit = Math.floor(len / 2);
            rightsplit = Math.floor(len / 2);
 
 
            left = string.substring(0, leftsplit);
            left = left.substring(0, (left.length - cutleft));
 
            right = string.substring(rightsplit);
            right = right.substring(cutright);
 
            return left+cutsymbol+right;
        } else {
            return string.substring(0, length);
        }
    } else {
        return string;
    }
*/
}
?>