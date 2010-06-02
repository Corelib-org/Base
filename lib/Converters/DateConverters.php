<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib date converters.
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


//*****************************************************************//
//*************** DateConverterSmartFormat class ******************//
//*****************************************************************//
/**
 * Convert unixtime to human readable date.
 *
 * This converter is based on php's function strftime. It has three
 * date formats it can output. a format for dates that is today.
 * a format for a date within this year and a format for everything else.
 *
 * @link http://dk.php.net/strftime
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class DateConverterSmartFormat implements Converter {


	//*****************************************************************//
	//********** DateConverterSmartFormat class properties ************//
	//*****************************************************************//
	/**
	 * Date format for today
	 *
	 * @var string
	 * @internal
	 */
	private $format_today = null;

	/**
	 * Date format for this year
	 *
	 * @var string
	 * @internal
	 */
	private $format_year = null;

	/**
	 * Date format for other dates
	 *
	 * @var string
	 * @internal
	 */
	private $format_other = null;


	//*****************************************************************//
	//*********** DateConverterSmartFormat class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $today date format for today
	 * @param string $year date format for this year
	 * @param string $other date format for other dates
	 * @return void
	 */
	public function __construct($today, $year, $other){
		$this->format_today = $today;
		$this->format_year = $year;
		$this->format_other = $other;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data){
		if($data >= mktime(0, 0, 0, date('n'), date('j'), date('Y')) && $data <= mktime(23, 59, 59, date('n'), date('j'), date('Y'))){
			// Timestamp for today
			return strftime($this->format_today, $data);

		} else if($data >= mktime(0, 0, 0, 1, 1, date('Y')) && $data <= mktime(23, 59, 59, 12, 31, date('Y')) ){
			// Timestamp for this year
			return strftime($this->format_year, $data);

		} else {
			// Timestamp for other
			return strftime($this->format_other, $data);
		}
	}
}
?>
