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
	
	public function getResource($handler, $resource){
		$manager = Manager::getInstance();
		$resource = $manager->getResource($handler, $resource);
		$extension = substr($resource, -3);
		switch ($extension){
			case 'css':
				header('Content-Type: text/css');
				echo file_get_contents($resource);
				break;
			case 'jpg' || 'epg':
				header('Content-Type: image/jpeg');
				echo file_get_contents($resource);
				break;
			case 'gif':
				header('Content-Type: image/gif');
				echo file_get_contents($resource);
				break;
			case 'png':
				header('Content-Type: image/png');
				echo file_get_contents($resource);
				break;
			case '.js':
				header('Content-Type: text/javascript');
				echo file_get_contents($resource);
				break;
			default:
				trigger_error('Illegal Resource type!', E_USER_ERROR);
		}
		exit;
	}
}
?>