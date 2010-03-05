<?php
class WebPage extends DummyPageGet  {
	public function build(){
		$this->xsl->addTemplate('pages/index.xsl');
		$this->addTemplateDefinition($this->xsl);
	}
}
?>