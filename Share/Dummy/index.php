<?php
include_once('etc/config.php');

$eventHandler = EventHandler::getInstance();
$eventHandler->triggerEvent(new EventRequestStart());

include_once('etc/abstracts.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	include_once('etc/post.php');
	PageFactory::set_no_referer();
} else {
	include_once('etc/get.php');
}

if(!isset($_GET['page'])){
	$_GET['page'] = '/';
}

if(substr($_GET['page'], -1) != '/'){
	$_GET['page'] .= '/';
}

if(!isset($pages[$_GET['page']])){
	include_once($pages['/corelib/errors/404/']);
} else {
	include_once($pages[$_GET['page']]);
}

try {
	if(class_exists('WebPage')){
		$page = PageFactory::getInstance();
		$page->build(new WebPage());
	} else {
		throw new BaseException('Could not find WebPage Class.');
	}
} catch (BaseException $e) {
	echo $e;
}

if(BaseException::IsErrorThrown()){
	echo BaseException::getErrorPage();
} else {
	$page->draw();
}

$eventHandler->triggerEvent(new EventRequestEnd());
?>