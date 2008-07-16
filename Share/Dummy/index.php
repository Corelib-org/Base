<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
require_once('etc/config.php');
require_once('etc/abstracts.php');

$eventHandler = EventHandler::getInstance();
$eventHandler->triggerEvent(new EventRequestStart());

$page = PageFactory::getInstance();
$page->resolvePageObject();
		
try {
	if(class_exists(PAGE_FACTORY_CLASS_NAME, false)){
		eval('$page->build(new '.PAGE_FACTORY_CLASS_NAME.'());');
	} else {
		throw new BaseException('Could not find '.PAGE_FACTORY_CLASS_NAME.' Class.');
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