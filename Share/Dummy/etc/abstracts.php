<?php
/*
 * Setup Language
 */
if(defined('ABSTRACTS_ENABLE_LANGUAGE') && ABSTRACTS_ENABLE_LANGUAGE){
	$language = Language::getInstance();
	$language->setLanguageMap(array('en'));
	$language->setLanguageFileBase('rescources/language/');

	if(!defined('HTTP_STATUS_MESSAGE_FILE')){
		define('HTTP_STATUS_MESSAGE_FILE', $language->getLanguageFilePath().'status.xml');
	}
} else {
	define('ABSTRACTS_ENABLE_LANGUAGE', false);
}

//$base->loadClass('UsersAuthorization');
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
