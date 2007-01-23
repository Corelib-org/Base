<?php
class WebPage extends MyPage  {
	public function build(){
		$this->xsl->addTemplate('pages/index.xsl');
		$this->addTemplateDefinition($this->xsl);
	}
}
?>