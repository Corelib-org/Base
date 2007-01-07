<?php

/* 
 * Setup Language 
 */
$language = Language::getInstance();
$language->setLanguageMap(array('en'));
$language->setLanguageFileBase('rescources/language/');

if(!defined('HTTP_STATUS_MESSAGE_FILE')){
	define('HTTP_STATUS_MESSAGE_FILE', $language->getLanguageFilePath().'status.xml');
}

/*
 *	Setup event Handler (developer only)
 */
/* UNCOMMENT TO ADD USER AUTHORIZATION FEATURES
$eventHandler = EventHandler::getInstance();
$eventHandler->registerObserver(new UsersAuthorizationConfirmEvent());
$eventHandler->registerObserver(new UsersAuthorizationStoreEvent());
$eventHandler->registerObserver(new UsersAuthorizationPutSettingsXML());
*/

abstract class MyPage extends Page {
	protected $xsl = null;
	
	function __construct() {
		$this->xsl = new PageFactoryDOMXSLTemplate();
		$this->addSettings(Language::getInstance());
	}
}
?>
