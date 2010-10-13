<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory XML Tools
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
 * @version 1.0.0 ($Id: GenericXMLOutput.php 5169 2010-03-03 12:46:16Z wayland $)
 */

//*****************************************************************//
//************************* XMLTools class ************************//
//*****************************************************************//
/**
 * XML Tools.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class XMLTools {

	//*****************************************************************//
	//******************** XMLTools class methods *********************//
	//*****************************************************************//
	/**
	 * Escape XML Charecters.
	 *
	 * @param string $string source string
	 * @return string escaped string
	 */
	static public function escapeXMLCharacters($string){
		if(version_compare(PHP_VERSION, '5.2.3') > -1){
			return htmlspecialchars($string, null, 'UTF-8', false);
		} else {
			return htmlspecialchars($string, null, 'UTF-8');
		}
	}

	/**
	 * Create page xml.
	 *
	 * Create XML representation of a paging list of
	 * a set of records. This method is used by the
	 * record list classes.
	 *
	 * @param DOMDocument $xml
	 * @param integer $count row count
	 * @param interger $per_page_count rows per page
	 * @param integer $current_page current page
	 * @return DOMElement
	 */
	static public function makePagerXML(DOMDocument $xml, $count, $per_page_count=20, $current_page){
		$pager = $xml->createElement('pager');
		$pager->setAttribute('count', $count);
		$pager->setAttribute('per-page-count', $per_page_count);
		$pager->setAttribute('offset', $per_page_count * ($current_page - 1));
		for ($i = 1; $i <= ceil($count / $per_page_count); $i++){
			$page = $pager->appendChild($xml->createElement('page', $i));
			if($i == $current_page){
				$page->setAttribute('current', 'true');
			}
		}
		return $pager;
	}

	/**
	 * Get elements by tag name from Output class.
	 *
	 * @param Output $output
	 * @param string $element name
	 * @return DOMNodeList
	 */
	static public function getElementsByTagNameFromOutput(Output $output, $element){
		$list = new DOMDocument('1.0', 'UTF-8');
		$list->appendChild($output->getXML($list));
		return $list->getElementsByTagName($element);
	}
}


//*****************************************************************//
//***************** PageFactoryDOMDocument class ******************//
//*****************************************************************//
/**
 * Page factory DOMDocument.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class PageFactoryDOMDocument extends DOMDocument {


	//*****************************************************************//
	//************ PageFactoryDOMDocument class methods ***************//
	//*****************************************************************//
	/**
	 * Create new DOMElement
	 *
	 * Return new instance of DOMElement linked to DOMDocument.
	 * The difference from this method to the original is that
	 * it will always do {@link XMLTools::escapeXMLCharacters()}
	 * on the content of the node.
	 *
	 * @see http://www.php.net/manual/en/domdocument.createelement.php
	 */
	public function createElement($name, $content=null){
		if(!is_null($content)){
			return parent::createElement($name, XMLTools::escapeXMLCharacters($content));
		} else {
			return parent::createElement($name);
		}
	}
}
?>