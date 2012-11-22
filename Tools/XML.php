<?php
namespace Corelib\Base\Tools;
use Corelib\Base\PageFactory\DOMDocument;
/**
 * XML Tools.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class XML {

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
?>