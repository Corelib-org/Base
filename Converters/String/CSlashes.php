<?php

namespace Corelib\Base\Converters\Date;

use \Corelib\Base\Converters\Converter;

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
class CSlashes implements Converter {


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