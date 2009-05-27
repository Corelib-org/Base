<?php
class WebPage extends DummyPage  {
	public function build(){
		$this->xsl->addTemplate('pages/index.xsl');
		$this->addTemplateDefinition($this->xsl);
	}
}
?>