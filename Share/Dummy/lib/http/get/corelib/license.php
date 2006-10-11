<?php
class WebPage extends Page {
	
	public function build(){ 
		$xsl = new PageFactoryDOMXSLTemplate();
		$xsl->addTemplate('corelib/license.xsl');
		$xsl->addStyleSheet('/share/web/style/corelib/style.css');
		$this->addTemplateDefinition($xsl);			
	}
}
?>