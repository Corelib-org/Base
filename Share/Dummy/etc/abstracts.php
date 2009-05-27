<?php
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

abstract class DummyPage extends PageBase {
	protected $xsl = null;

	function __construct() {
		$this->xsl = new PageFactoryDOMXSLTemplate();
	}
}
?>
