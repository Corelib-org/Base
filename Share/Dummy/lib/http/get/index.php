<?php
class WebPage extends Page  {
	public function build(){
		$this->xsl->addTemplate('pages/index.xsl');
		$this->addTemplateDefinition($this->xsl);
	}
}
?>