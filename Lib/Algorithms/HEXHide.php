<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * HEXHide Class
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 * @filesource
 */

//*****************************************************************//
//************************ Table Of Content ***********************//
//*****************************************************************//
//**                                                        Line **//
//**    1. HEXHide Class ...................................     **//
//**        1. HEXHide Class Methods .......................     **//
//**            1. hide() ..................................     **//
//**            2. find() ..................................     **//
//**            3. _getHiddenOffset() ......................     **//
//**                                                             **//
//*****************************************************************//

//*****************************************************************//
//**'********************** HEXHide Class *************************//
//*****************************************************************//
/**
 * HEXHide implements a simple algorithm for hiding a number within a hex string
 * 
 * @todo Describe how the HEXHide encoding process works
 * @package corelib 
 */
class HEXHide {
	//*****************************************************************//
	//********************* HEXHide Class Methods *********************//
	//*****************************************************************//		
	/**
	 * Hide integer in HEX String
	 * 
	 * @param $number integer Interger to hide within a hex string
	 * @param $seed string setting a custom seed instead of letting HEXHide generate one
	 * @return string Hash with hidden number
	 */
	public static function hide($number, $seed=null){
		if(is_null($seed)){
			$seed = sha1(microtime()).md5(strrev(microtime()));
		}
		$offset = self::_getHiddenOffset($seed);
		$prefix = substr($seed, 0, $offset);
		$length = dechex(strlen($number));
		$end = substr($seed, strlen($prefix) + strlen($number)+strlen($length));
		return $prefix.$length.$number.$end;
	}
	
	/**
	 * Find integer in HEX String
	 * 
	 * @param $hex string HEX String containing the hidden number
	 * @return integer Hidden integer
	 */
	public static function find($hex){
		$offset = self::_getHiddenOffset($hex);
		$offset = substr($hex, $offset, strlen($hex));
		$endbytes = hexdec($offset{0});
		return (integer) substr($offset, 1, $endbytes);
	}
	
	/**
	 * Find HEX string offset
	 * 
	 * @todo Optimize HEXHide::_getHiddenOffset(), how the offset is calculated  
	 * @return integer offset
	 */
	private static function _getHiddenOffset($hex){
		$offset = hexdec($hex{0});
		if($offset == 0){
			$offset++;
		}
		$new_offset = hexdec($hex{$offset});
		if($new_offset == 0){
			$new_offset++;
		}
		$offset += $new_offset;
		$new_offset = hexdec($hex{$offset});
		if($new_offset == 0){
			$new_offset++;
		}
		$offset += $new_offset;
		return $offset;
	}
}
?>