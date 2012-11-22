<?php

namespace Corelib\Base\Converters\Date;

use Corelib\Base\Converters\Converter;

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
class Strftime implements Converter {


	//*****************************************************************//
	//**************** DateConverter class properties *****************//
	//*****************************************************************//
	/**
	 * @var string charecter list.
	 * @internal
	 */
	protected $format = null;

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
		if(!is_integer($data)){
			$timestamp = strptime($data, $this->format);
			$timestamp  = mktime($timestamp['tm_hour'], $timestamp['tm_min'], $timestamp['tm_sec'], ($timestamp['tm_mon'] + 1), $timestamp['tm_mday'], ($timestamp['tm_year']+1900));
			return $timestamp ;
		} else {
			return strftime($this->format, $data);
		}
	}
}