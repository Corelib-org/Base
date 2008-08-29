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
?>