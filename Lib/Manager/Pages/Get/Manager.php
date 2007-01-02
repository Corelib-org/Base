<?php
class WebPage extends ManagerPage {
	public function build(){
		
	}
	
	public function redirect(){
		$this->xsl->setLocation('/corelib/manager/dashboard/');
	}
	
	public function dashboard(){
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/manager/dashboard.xsl');
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
			default:
				trigger_error('Illegal Resource type!', E_USER_ERROR);
		}
		exit;
	}
}
?>