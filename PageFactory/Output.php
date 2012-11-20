<?php
namespace Corelib\Base\PageFactory;

/**
 * Output interface
 *
 * This is the blue print for output classes.
 *
 * @category corelib
 * @package Base
 */
interface Output {


	//*****************************************************************//
	//******************* Output interface methods ********************//
	//*****************************************************************//
	/**
	 * Get output XML.
	 *
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	public function getXML(\DOMDocument $xml);
}
?>