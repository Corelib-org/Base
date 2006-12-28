<?php
require_once('etc/config.php');
require_once('etc/abstracts.php');

$eventHandler = EventHandler::getInstance();
$eventHandler->triggerEvent(new EventRequestStart());

$page = PageFactory::getInstance();
$page->resolvePageObject();
		
try {
	if(class_exists('WebPage', false)){
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