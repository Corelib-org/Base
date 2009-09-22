<?php
class WebPage extends ManagerPage {
	public function build(){

	}

	public function redirect(){
		$this->xsl->setLocation('/corelib/manager/dashboard/');
	}

	public function about(){
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/manager/about.xsl');
	}
	public function markup(){
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/manager/markup.xsl');
	}

	public function dashboard(){
		$this->addContent(new ManagerDashboard($this->xsl));
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/manager/dashboard.xsl');
		$this->xsl->addStyleSheet('corelib/resource/manager/css/dashboard.css');
		$this->xsl->addStyleSheet('corelib/resource/manager/css/widgets.css');
		$this->xsl->addJavaScript('corelib/resource/manager/javascript/widgets.js');
	}
}
?>