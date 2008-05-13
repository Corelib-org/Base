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

if(defined('ABSTRACTS_ENABLE_DATABASE') && ABSTRACTS_ENABLE_DATABASE){
	if(!defined('DATABASE_MASTER_CHARSET')){
		define('DATABASE_MASTER_CHARSET', 'utf8');
	}
	$masterdb = new MySQLiEngine(DATABASE_MASTER_HOSTNAME,
	                             DATABASE_MASTER_USERNAME,
	                             DATABASE_MASTER_PASSWORD,
	                             DATABASE_MASTER_DATABASE, 
	                             false, 
	                             DATABASE_MASTER_CHARSET);
	$dbms = Database::getInstance();
	$dbms->masterConnect($masterdb);
} else if(!defined('ABSTRACTS_ENABLE_DATABASE')){
	define('ABSTRACTS_ENABLE_DATABASE', false);
}

abstract class WebPage extends PageBase {
	protected $xsl = null;

	function __construct() {
		$this->xsl = new PageFactoryDOMXSLTemplate();
	}
}
?>
