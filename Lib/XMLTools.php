<?php
class XMLTools {
	static public function escapeXMLCharecters($string){
		return htmlspecialchars($string, null, 'UTF-8', false);
	}
}
?>