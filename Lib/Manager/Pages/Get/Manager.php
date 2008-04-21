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