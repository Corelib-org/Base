<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory Generic XML Output
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
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */


//*****************************************************************//
//************************ XMLOutput class ************************//
//*****************************************************************//
/**
 * Page factory page base.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class XMLOutput implements Output {


	//*****************************************************************//
	//******************* XMLOutput class properties ******************//
	//*****************************************************************//
	/**
	 * @var DOMElement
	 * @internal
	 */
	private $xml;

	/**
	 * @var DOMDocument
	 * @internal
	 */
	private static $DOMDocument = null;


	//*****************************************************************//
	//******************** XMLOutput class methods ********************//
	//*****************************************************************//
 	/**
	 * Create new instance.
	 *
	 * @return void
	 */
	public function __construct(){
		if(!self::$DOMDocument instanceof DOMDocument){
			self::$DOMDocument = new DOMDocument('1.0', 'UTF-8');
		}
	}
	/**
	 * Create DOMElement.
	 *
	 * @see http://www.php.net/manual/en/domdocument.createelement.php
	 * @param string $element name
	 * @param string $content content
	 * @return DOMElement
	 */
	public function createElement($element, $content=null){
		return self::$DOMDocument->createElement($element, $content);
	}

	/**
	 * Set XML Element.
	 *
	 * @param DOMElement $xml
	 * @return DOMElement
	 */
	public function setXML(DOMElement $xml){
		$this->xml = $xml;
		return $this->xml;
	}

	/**
	 * Set XML read from a XML file.
	 *
	 * @param string $filename
	 * @return DOMElement
	 */
	public function setXMLDocumentFile($filename){
		$document = new DOMDocument('1.0', 'UTF-8');
		$document->load($filename);
		return $this->setXML($document->documentElement);
	}

	/**
	 * Get XML Content.
	 *
	 * @see Output::getXML($xml)
	 */
	public function getXML(DOMDocument $xml){
		$this->xml = $xml->importNode($this->xml, true);
		return $this->xml;
	}
}
?>