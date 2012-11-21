<?php

namespace Corelib\Base\Converters\Date;

use \Corelib\Base\Converters\Converter;

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