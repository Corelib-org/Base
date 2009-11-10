<?php
class WebPage extends ManagerPage {
	public function __init(){ }

	public function build(){

	}

	public function getResource($handler, $resource){
		$manager = Manager::getInstance();
		$resource = $manager->getResource($handler, $resource);
		$extension = substr($resource, -3);

		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', filemtime($resource)));
 		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
		header('Cache-Control: public, max-age=86400');
		header('Pragma:');

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