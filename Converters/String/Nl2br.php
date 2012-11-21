<?php

namespace Corelib\Base\Converters\Date;

use \Corelib\Base\Converters\Converter;

/**
 * Convert Newlines to XHTML line breaks.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class Nl2br implements Converter {


	//*****************************************************************//
	//************ StringConverterNl2br class properties **************//
	//*****************************************************************//
	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data) {
		return nl2br($data);
	}
}