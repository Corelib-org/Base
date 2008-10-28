<?php
class WebPage extends ManagerPage {
	public function build(){

	}

	public function database(){
		$this->addContent(new DatabaseTool());
		$this->xsl->addJavaScript('corelib/resource/manager/javascript/database.js');
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/system/database.xsl');
	}
	
	public function check(){
		$config = ManagerConfig::getInstance();
		$this->addContent($config->getSystemCheckResults());
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/system/check.xsl');
	}
	
	public function reload(){
		unlink(MANAGER_EXTENSION_FILE);
		$this->xsl->setLocation('corelib/system/check/');
	}
	
	public function configuration(){
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/system/configuration.xsl');

//		$config = Configuration::getInstance();
/*		echo '<pre>';
		print_r($config);
		exit; */
		// $this->addContent($config);
		
		$this->addContent(ManagerConfig::getInstance()->getPropertyOutput('constants'));
		
	}
}
?>