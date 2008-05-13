<?php
class WebPage extends ManagerPage {
	public function build(){

	}

	public function configuration(){
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/system/configuration.xsl');

		$config = Configuration::getInstance();
/*		echo '<pre>';
		print_r($config);
		exit; */
		$this->addContent($config);
	}
}
?>