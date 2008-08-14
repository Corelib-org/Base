<?php
class XMLTools {
	static public function escapeXMLCharacters($string){
		return htmlspecialchars($string, null, 'UTF-8', false);
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
}
?>