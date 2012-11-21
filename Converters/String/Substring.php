<?php

namespace Corelib\Base\Converters\Date;

use \Corelib\Base\Converters\Converter;

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
class Substring implements Converter {


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
?>