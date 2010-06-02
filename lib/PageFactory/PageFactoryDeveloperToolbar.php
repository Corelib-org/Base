<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page Factory Developer toolbar.
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
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 * @since Version 5.0
 *
 * @todo Write implementation example for {@link PageFactoryDeveloperToolbarItem}
 */


//*****************************************************************//
//************ PageFactoryDeveloperToolbarItem Class **************//
//*****************************************************************//
/**
 * Page Factory Developer toolbar item.
 *
 * This abstract class should be extended in order to
 * create a new toolbar item.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
abstract class PageFactoryDeveloperToolbarItem {


	//*****************************************************************//
	//***** PageFactoryDeveloperToolbarItem Class abstract methods ****//
	//*****************************************************************//
	/**
	 * Get toolbar item html code.
	 *
	 * This method should return the html code to represent
	 * what ever should be displayed in the toolbar. eg. a
	 * image or a count of something. Clicking this item will
	 * display whatever {@link PageFactoryDeveloperToolbarItem::getContent()}
	 * returns.
	 *
	 * @return string html
	 */
	abstract public function getToolbarItem();


	//*****************************************************************//
	//********** PageFactoryDeveloperToolbarItem Class methods ********//
	//*****************************************************************//
	/**
	 * Get item content.
	 *
	 * Return html content for whatever should be displayed when
	 * the toolbar item is clicked.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 * @return mixed boolean false if no content, else return string
	 */
	public function getContent(){
		return false;
	}
}


//*****************************************************************//
//*************** PageFactoryDeveloperToolbar Class ***************//
//*****************************************************************//
/**
 * Page Factory Developer toolbar.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
final class PageFactoryDeveloperToolbar implements Singleton {


	//*****************************************************************//
	//********* PageFactoryDeveloperToolbar Class properties **********//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var PageFactoryDeveloperToolbar
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Toolbar items.
	 *
	 * @var array toolbar items
	 */
	private $items = array();

	/**
	 * @ignore
	 */
	private function __construct(){ }

	/**
	 * 	Return instance of PageFactoryDeveloperToolbar.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses PageFactoryDeveloperToolbar::$instance
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

	/**
	 * Create toolbar.
	 *
	 * Generate toolbar when object is echoed.
	 *
	 * @return string toolbar.
	 * @internal
	 */
	public function __toString(){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL && (!defined('PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR') || PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR == true)){
			$headers = headers_list();
			$show_bar = false;
			foreach($headers as $header){
				if(stristr($header, 'Content-Type') && stristr($header, 'text/html')){
					$show_bar = true;
					break;
				}
			}

			if($show_bar){
				$data = '';
				$toolbar = '';
				foreach($this->items as $item){
					$id = 'developer-toolbar-'.RFC4122::generate();
					$tool = $item->getToolbarItem();
					if($content = $item->getContent()){
						$toolbar .= '<a onclick="if(document.getElementById(\''.$id.'\').style.display == \'none\'){ document.getElementById(\''.$id.'\').style.display = \'block\' } else { document.getElementById(\''.$id.'\').style.display = \'none\' }">'.$tool.'</a	> &nbsp; ';
						$data .= '<div id="'.$id.'" style="display: none;">'.$content.'</div>';
					} else {
						$toolbar .= $tool.'  &nbsp;  ';
					}
				}

				$output  = '<link rel="stylesheet" type="text/css" href="corelib/resource/manager/css/toolbar.css" />';
				$output .= '<div id="developer-toolbar">';
				$output .= '<div style="float: right"><a href="javascript:void(0)" onclick="document.getElementById(\'developer-toolbar\').style.display=\'none\';"><img src="corelib/resource/manager/images/icons/toolbar/close.png" alt="Close toolbar" title="Close toolbar" style="border: 0px;"/></a>&#160;</div>';
				$output .= '<div class="toolbar">'.$toolbar.'</div><div class="data">'.$data.'</div></div>';
				return $output;
			}
		}
		return '';
	}
}
?>