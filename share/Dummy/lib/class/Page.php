<?php

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
 */
abstract class Page extends \Corelib\Base\PageFactory\Page {
	public static function checkAuthString($string){
		if(isset($_GET['auth']) && $_GET['auth'] == $string){
			return true;
		}
		return false;
	}
}

/**
 * Base GET page/request controller.
 *
 * Here you can define generic methods used
 * around the controller layer when handling a
 * HTTP GET request
 *
 * @package Dummy
 * @since Version 5.0
 */
abstract class PageGet extends Page {
	/**
	 * PageFactory DOM XSL Template instance.
	 *
	 * @var Corelib\Base\PageFactory\Templates\XSLT
	 */
	protected $xsl = null;

	/**
	 * Prepare page.
	 *
	 * @uses Corelib\Base\PageFactory\Templates\XSLT
	 * @uses DummyPageGet::$xsl
	 * @return void
	 */
	function __init() {
		$this->xsl = new \Corelib\Base\PageFactory\Templates\XSLT();
		$this->setTemplate($this->xsl);
	}

	/**
	 * Get current page from url.
	 *
	 * @param string $inputvar http get variable name
	 * @return integer page
	 */
	public function getPagingPage($inputvar = 'p'){
		$input = Locator::get('Corelib\Base\Input\Handler');
		if($input->validateGet($inputvar, new \Corelib\Base\Input\Validators\Regex('/^[0-9]+$/'))) {
			return (int) $input->getGet($inputvar);
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
 * @since Version 5.0
 */
abstract class PagePost extends Page {
	/**
	 * PageFactory Post Template instance.
	 *
	 * @var Corelib\Base\PageFactory\Templates\POST
	 */
	protected $post = null;

	/**
	 * Prepare page.
	 *
	 * @uses Corelib\Base\PageFactory\Templates\POST
	 * @uses DummyPagePost::$post
	 * @return void
	 */
	function __init() {
		$this->post = new \Corelib\Base\PageFactory\Templates\POST();
		$this->setTemplate($this->post);
	}
}