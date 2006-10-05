<?php
class WebPage extends Page {
	
	public function build(){ 
		
	}
	
	public function prebuild(){
		$this->addCSSStyleSheet('/share/web/style/corelib/style.css');
		$this->setXSLTStyleSheet('../corelib/license.xsl');	
	}
}
?>