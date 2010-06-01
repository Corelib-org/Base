<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib default converters.
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Base.php 5066 2009-09-24 09:32:09Z wayland $)
 */


//*****************************************************************//
//********************* Converter interface ***********************//
//*****************************************************************//
/**
 * Converter interface.
 *
 * Implement this interface in order to create a new
 * Converter class, compatible with auto generated model
 * classes.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
interface Converter {


	//*****************************************************************//
	//***************** Converter interface methods *******************//
	//*****************************************************************//
	/**
	 * Convert data.
	 *
	 * This usually take a non complex data type
	 * like a integer, float or string. Convert the data
	 * and return the converted data.
	 *
	 * @param mixed $data
	 * @return mixed converted data
	 */
	public function convert($data);
}


//*****************************************************************//
//******************** ConverterChain class ***********************//
//*****************************************************************//
/**
 * Converter chain.
 *
 * The converter chain class allows for multiple converters
 * to be treated as one. meaning all conversion will be made in
 * the order there are added using {@link ConverterChain::addConveter()}.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class ConverterChain implements Converter {


	//*****************************************************************//
	//************** ConverterChain class properties ******************//
	//*****************************************************************//
	/**
	 * @var array list of converters
	 * @internal
	 */
	private $converters = array();


	//*****************************************************************//
	//**************** ConverterChain class methods *******************//
	//*****************************************************************//
	/**
	 * Add converter to converter chain.
	 *
	 * @param Converter $converter
	 * @return boolean true on success, else return false
	 */
	public function addConveter(Converter $converter){
		$this->converters[] = $converter;
		return true;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data){
		foreach ($this->converters as $converter){
			$data = $converter->convert($data);
		}
		return $data;
	}
}


//*****************************************************************//
//***************** StringConverterNl2br class ********************//
//*****************************************************************//
/**
 * Convert Newlines to XHTML line breaks.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class StringConverterNl2br implements Converter {


	//*****************************************************************//
	//************ StringConverterNl2br class properties **************//
	//*****************************************************************//
	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data) {
		return nl2br($data);
	}
}


//*****************************************************************//
//************** StringConverterAddCSlashes class *****************//
//*****************************************************************//
/**
 * Add slashes to data.
 *
 * This converter is the equivalent of php's own addcslashes function.
 *
 * @link http://dk.php.net/addcslashes
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class StringConverterAddCSlashes implements Converter {


	//*****************************************************************//
	//********** StringConverterAddCSlashes class properties **********//
	//*****************************************************************//
	/**
	 * @var string charecter list.
	 * @internal
	 */
	private $charlist = null;


	//*****************************************************************//
	//*********** StringConverterAddCSlashes class methods ************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $charlist charecter list to escape
	 * @return void
	 */
	public function __construct($charlist='\''){
		$this->charlist = $charlist;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data) {
		return addcslashes($data, $this->charlist);
	}
}


//*****************************************************************//
//*************** StringConverterSubstring class ******************//
//*****************************************************************//
/**
 * Perform substring on data.
 *
 * This converter is almost the equivalent of php's
 * own substr function. However with some added features.
 *
 * This converter can do normal substring convertion or use a smart
 * algorithm which instead of just cutting at the end of a string, it cuts
 * in the middle, converting a long string or word from "this-is-a-very-long-word"
 * to "this-i...g-word".
 *
 * @link http://dk.php.net/substr
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class StringConverterSubstring implements Converter {


	//*****************************************************************//
	//********** StringConverterSubstring class properties ************//
	//*****************************************************************//
	/**
	 * @var integer string length
	 * @internal
	 */
	private $length = null;

	/**
	 * @var string cut symbol
	 * @internal
	 */
	private $cutsymbol = '...';

	/**
	 * @var boolean if true use the smart substring algorithm
	 * @internal
	 */
	private $smart = false;
	/**
	 * @var boolean if true substring will be performed with wordsafety.
	 * @internal
	 */
	private $wordsafe = false;


	//*****************************************************************//
	//*********** StringConverterSubstring class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param integer $length string max length
	 * @param boolean $smart if true use the smart substring algorithm
	 * @param boolean $wordsafe if true do wordsafe substring, this does not apply if the smart algorithm is used
	 * @param string $cutsymbol cut symbol to insert where the cut is made.
	 * @return void
	 */
	public function __construct($length, $smart=false, $wordsafe=true, $cutsymbol='...'){
		$this->length = $length;
		$this->cutsymbol = $cutsymbol;
		$this->smart = $smart;
		$this->wordsafe = $wordsafe;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
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

	/**
	 * Smart substring agorithm.
	 *
	 * @param string $data
	 * @return string
	 * @internal
	 */
	private function _smartSubstring($data){
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

	/**
	 * Normal substring conversion.
	 *
	 * @param string $data
	 * @param integer max string length
	 * @return string
	 * @internal
	 */
	private function _substr($string, $length){
		if($this->wordsafe){
			while($string{$length} != ' '){
				$length++;
				if($length > strlen($string) || (strlen($string) - 1) < $length){
					break;
				}
			}
		}
		return substr($string, 0, $length);
	}
}


//*****************************************************************//
//**************** StringConverterFileSize class ******************//
//*****************************************************************//
/**
 * Convert file size to human readable format.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class StringConverterFileSize implements Converter {


	//*****************************************************************//
	//*********** StringConverterFileSize class properties ************//
	//*****************************************************************//
	/**
	 * @var integer decimal precision
	 * @internal
	 */
	private $precision = null;


	//*****************************************************************//
	//************ StringConverterFileSize class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param integer $precision decimal precision
	 * @return void
	 */
	public function __construct($precision = 2){
		$this->precision = $precision;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
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


//*****************************************************************//
//********************** DateConverter class *********************//
//*****************************************************************//
/**
 * Convert unixtime to human readable date.
 *
 * This converter is based on php's function strftime
 *
 * @link http://dk.php.net/strftime
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class DateConverter implements Converter {


	//*****************************************************************//
	//**************** DateConverter class properties *****************//
	//*****************************************************************//
	/**
	 * @var string charecter list.
	 * @internal
	 */
	private $format = null;

	private $date_format = null;

	//*****************************************************************//
	//***************** DateConverter class methods *******************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $format regular expression
	 * @return void
	 */
	public function __construct($format){
		$this->format = $format;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data) {
		return strftime($this->format, $data);
	}
}


//*****************************************************************//
//*************** DateConverterParseFormat class ******************//
//*****************************************************************//
/**
 * Convert string to unixtime.
 *
 * This converter is based on php's function strptime
 * however it incorporated format parsing from a regular expression
 * with named captures, the capture names should be the ones used in
 * php's strftime function without the % prefix.
 *
 * @link http://dk.php.net/strftime
 * @link http://dk.php.net/strptime
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class DateConverterParseFormat implements Converter {


	//*****************************************************************//
	//********** DateConverterParseFormat class properties ************//
	//*****************************************************************//
	/**
	 * @var string charecter list.
	 * @internal
	 */
	private $format = null;

	private $date_format = null;

	//*****************************************************************//
	//*********** DateConverterParseFormat class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $format regular expression
	 * @return void
	 */
	public function __construct($format){
		$this->format = $format;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data) {
		if(!is_integer($data)){
			preg_match($this->format, $data, $matches);
			$format = $data;
			foreach ($matches as $key => $match){

				if(is_string($key)){
					$format = preg_replace('/'.$match.'/', '%'.$key, $format, 1);
				}
			}

			$date = strptime($data, $format);
			$date = mktime($date['tm_hour'], $date['tm_min'], $date['tm_sec'], ($date['tm_mon'] + 1), $date['tm_mday'], ($date['tm_year']+1900));
			return $date;
		} else {
			if(is_null($this->date_format)){
				$delimiter = $this->format{0};
				$this->date_format = preg_replace('/^.*?#format:(.*?)\)'.preg_quote($delimiter, '/').'$/', '\\1', $this->format);
			}
			return strftime($this->date_format, $data);
		}
	}
}
?>