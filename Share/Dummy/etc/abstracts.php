<?php
/*
 * Setup Language
 */
if(defined('ABSTRACTS_ENABLE_LANGUAGE') && ABSTRACTS_ENABLE_LANGUAGE){
	$language = Language::getInstance();
	$language->setLanguageMap(array('en'));
	$language->setLanguageFileBase('share/lang/');

	if(!defined('HTTP_STATUS_MESSAGE_FILE')){
		define('HTTP_STATUS_MESSAGE_FILE', $language->getLanguageFilePath().'status.xml');
	}
} else if(!defined('ABSTRACTS_ENABLE_LANGUAGE')){
	define('ABSTRACTS_ENABLE_LANGUAGE', false);
}

if(defined('ABSTRACTS_ENABLE_AUTHORIZATION') && ABSTRACTS_ENABLE_AUTHORIZATION){
	$base->loadClass('UsersAuthorization');
} else if(!defined('ABSTRACTS_ENABLE_AUTHORIZATION')){
	define('ABSTRACTS_ENABLE_AUTHORIZATION', false);
}

abstract class MyPage extends Page {
	protected $xsl = null;

	function __construct() {
		$this->xsl = new PageFactoryDOMXSLTemplate();
	}
}
?>
