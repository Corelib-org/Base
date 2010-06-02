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
?>