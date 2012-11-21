<?php
namespace Corelib\Base\Converters;


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
?>