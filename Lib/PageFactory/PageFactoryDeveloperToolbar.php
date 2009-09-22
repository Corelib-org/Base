<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactoryDeveloperToolbar
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the
 *	Bravura Distribution license that is available through the
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura License and are
 *	unable to obtain it through the web, please send a note to
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 *
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: PageFactory.php 5058 2009-09-21 08:11:24Z wayland $)
 */


/**
 * @author Steffen Sørensen <ss@corelib.org>
 */
abstract class PageFactoryDeveloperToolbarItem {
	public function getToolbarItem(){

	}

	public function getContent(){
		return false;
	}
}


/**
 * @author Steffen Sørensen <ss@corelib.org>
 * @package corelib
 * @subpackage Base
 */
class PageFactoryDeveloperToolbar implements Singleton {
	/**
	 *	@var PageFactoryDeveloperToolbar
	 */
	private static $instance = null;

	/**
	 * @var array toolbar items
	 */
	private $items = array();


	/**
	 * @return void
	 */
	private function __construct(){

	}



	/**
	 * Get PageFactoryDeveloperToolbar instance.
	 *
	 *	@return PageFactoryDeveloperToolbar
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new PageFactoryDeveloperToolbar();
		}
		return self::$instance;
	}

	/**
	 * Add toolbar item to toolbar.
	 *
	 * @param PageFactoryDeveloperToolbarItem $item
	 * @return PageFactoryDeveloperToolbarItem
	 */
	public function addItem(PageFactoryDeveloperToolbarItem $item){
		$this->items[] = $item;
		return $item;
	}

	public function __toString(){
		$toolbar = '';
		foreach($this->items as $item){

			if(!$content = $item->getContent()){
				$toolbar .= $item->getToolbarItem();
			}
		}

		$output  = '<link rel="stylesheet" type="text/css" href="corelib/resource/manager/css/toolbar.css" />';
		$output .= '<div id="developer-toolbar"><div class="toolbar">'.$toolbar.'</div></div>';
		return $output;
	}
}



?>