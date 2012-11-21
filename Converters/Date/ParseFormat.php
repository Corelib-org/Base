<?php

namespace Corelib\Base\Converters\Date;

use \Corelib\Base\Converters\Converter;


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
class ParseFormat implements Converter {


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
?>