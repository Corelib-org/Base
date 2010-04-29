<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib dummy website abstracts file
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package Dummy
 * @subpackage Website
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 * @filesource
 */


//*****************************************************************//
//**************** Abstract dummy help contants *******************//
//*****************************************************************//

// Check to see if ABSTRACTS_ENABLE_AUTHORIZATION is set true
// If set to true the UserAuthorization classes is automatically loaded
if(defined('ABSTRACTS_ENABLE_AUTHORIZATION') && ABSTRACTS_ENABLE_AUTHORIZATION){
	$base->loadClass('UserAuthorization');
} else if(!defined('ABSTRACTS_ENABLE_AUTHORIZATION')){
	/**
	 * Enable autoloading of UserAuthorization features.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('ABSTRACTS_ENABLE_AUTHORIZATION', false);
}

// Check to see if ABSTRACTS_ENABLE_DATABASE is set true
// If set to true the Database classes is automatically loaded
if(defined('ABSTRACTS_ENABLE_DATABASE') && ABSTRACTS_ENABLE_DATABASE){
	$masterdb = new MySQLiEngine(DATABASE_MASTER_HOSTNAME,
	                             DATABASE_MASTER_USERNAME,
	                             DATABASE_MASTER_PASSWORD,
	                             DATABASE_MASTER_DATABASE);
	$dbms = Database::getInstance();
	$dbms->masterConnect($masterdb);
} else if(!defined('ABSTRACTS_ENABLE_DATABASE')){
	/**
	 * Enable autoloading of Database features.
	 *
	 * @var boolean true if enabled, else false
	 */
	define('ABSTRACTS_ENABLE_DATABASE', false);
}


//*****************************************************************//
//**************** Control layer abstract classes *****************//
//*****************************************************************//
/**
 * Basic page/request controller.
 *
 * This abstract class should hold generic methods used
 * around the controller layer.
 *
 * @package Dummy
 * @subpackage Website
 */
abstract class DummyPage extends PageBase { }

/**
 * Base GET page/request controller.
 *
 * Here you can define generic methods used
 * around the controller layer when handling a
 * HTTP GET request
 *
 * @package Dummy
 * @subpackage Website
 * @since Version 5.0
 */
abstract class DummyPageGet extends DummyPage {
	/**
	 * PageFactory DOM XSL Template instance.
	 *
	 * @var PageFactoryDOMXSLTemplate
	 */
	protected $xsl = null;

	/**
	 * Prepare page.
	 *
	 * @uses PageFactoryDOMXSLTemplate
	 * @uses DummyPageGet::$xsl
	 * @return void
	 */
	function __init() {
		$this->xsl = new PageFactoryDOMXSLTemplate();
		$this->addTemplateDefinition($this->xsl);
	}

	/**
	 * Get current page from url.
	 *
	 * @param string $inputvar http get variable name
	 * @return integer page
	 */
	public function getPagingPage($inputvar = 'p'){
		$input = InputHandler::getInstance();
		if($input->validateGet('p',new InputValidatorRegex('/^[0-9]+$/'))) {
			return (int) $input->getGet('p');
		} else {
			return 1;
		}
	}
}

/**
 * Base POST page/request controller.
 *
 * Here you can define generic methods used
 * around the controller layer when handling a
 * HTTP POST request
 *
 * @package Dummy
 * @subpackage Website
 * @since Version 5.0
 */
abstract class DummyPagePost extends DummyPage {
	/**
	 * PageFactory Post Template instance.
	 *
	 * @var PageFactoryPostTemplate
	 */
	protected $post = null;

	/**
	 * Prepare page.
	 *
	 * @uses PageFactoryPostTemplate
	 * @uses DummyPagePost::$post
	 * @return void
	 */
	function __init() {
		$this->post = new PageFactoryPostTemplate();
		$this->addTemplateDefinition($this->post);
	}
}
?>