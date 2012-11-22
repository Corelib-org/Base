<?php
namespace Corelib\Base\Tools;

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
 * @category corelib
 * @package Base
 * @subpackage Algorithms
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//************************* HEXHide Class *************************//
//*****************************************************************//
/**
 * HEXHide class.
 *
 * HEXHide implements a simple algorithm for hiding a number within
 * a base16 string.
 *
 * @todo Describe how the HEXHide encoding process works
 * @category corelib
 * @package Base
 * @subpackage Algorithms
 */
class HEXHide {


	//*****************************************************************//
	//********************* HEXHide Class Methods *********************//
	//*****************************************************************//
	/**
	 * Hide integer in HEX String.
	 *
	 * @see HEXHide::hide()
	 * @uses HEXHide::_getHiddenOffset()
	 * @param $number integer Interger to hide within a hex string
	 * @param $seed string setting a custom seed instead of letting HEXHide generate one
	 * @return string Hash with hidden number
	 */
	public static function hide($number, $seed=null){
		assert('is_integer($number)');
		assert('is_null($seed) || is_string($seed)');
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
	 * Find integer in HEX String.
	 *
	 * @param $hex string HEX String containing the hidden number
	 * @uses HEXHide::_getHiddenOffset()
	 * @see HEXHide::hide()
	 * @return integer Hidden integer
	 */
	public static function find($hex){
		assert('is_string($hex)');
		$offset = self::_getHiddenOffset($hex);
		$offset = substr($hex, $offset, strlen($hex));
		$endbytes = hexdec($offset{0});
		return (integer) substr($offset, 1, $endbytes);
	}

	/**
	 * Find HEX string offset.
	 *
	 * @todo Optimize HEXHide::_getHiddenOffset(), how the offset is calculated
	 * @return integer offset
	 * @internal
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