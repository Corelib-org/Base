<?php
namespace Corelib\Base\PageFactory\Toolbar;

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
abstract class Item {

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

?>