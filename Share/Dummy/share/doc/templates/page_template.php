<?php
class WebPage extends Page {
	
	public function build(){ }
	
	public function prebuild(){
		$this->setXSLTStyleSheet('../some/xsl/stylesheet.xsl');	
	}
}
?>