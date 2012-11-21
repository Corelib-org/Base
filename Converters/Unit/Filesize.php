<?php
namespace Corelib\Base\Converters\Unit;

use \Corelib\Base\Converters\Converter;

/**
 * Convert file size to human readable format.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class FileSize implements Converter {


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