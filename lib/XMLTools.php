<?php
/**
 * @todo This should be a part of PageFactory
 */
class XMLTools {
	static public function escapeXMLCharacters($string){
		if(version_compare(PHP_VERSION, '5.2.3') > -1){
			return htmlspecialchars($string, null, 'UTF-8', false);
		} else {
			return htmlspecialchars($string, null, 'UTF-8');
		}
	}
	
	static public function makePagerXML(DOMDocument $xml, $count, $per_page_count=20, $current_page){
		$pager = $xml->createElement('pager');
		for ($i = 1; $i <= ceil($count / $per_page_count); $i++){
			$page = $pager->appendChild($xml->createElement('page', $i));
			if($i == $current_page){
				$page->setAttribute('current', 'true');
			}
		}
		return $pager;
	}

	static public function getElementsByTagNameFromOutput(Output $output, $elements){
		$list = new DOMDocument('1.0', 'UTF-8');
		$list->appendChild($output->getXML($list));
		return $list->getElementsByTagName($elements);
	}
	
}
?>