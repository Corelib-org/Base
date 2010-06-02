<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib unit converters.
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
 * @version 1.0.0 ($Id$)
 */

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
?>