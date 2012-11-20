<?php
namespace Corelib\Base\PageFactory\Events;

/**
 * Apple default settings.
 *
 * This event is triggered when the page is drawn
 * making it possible to inject content into a page
 * automatically.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 * @author Steffen Sørensen <ss@corelib.org>
 */
class ApplySettings implements \Event {

	//*****************************************************************//
	//******* EventApplyDefaultSettings event class properties ********//
	//*****************************************************************//
	/**
	 * @var PageBase
	 * @internal
	 */
	private $page = null;

	/**
	 * Create new instance of object.
	 *
	 * @param PageBase $page
	 * @return void
	 */
	public function __construct(\Corelib\Base\PageFactory\Page $page){
		$this->page = $page;
	}

	/**
	 * Get current page.
	 *
	 * @return Page
	 */
	public function getPage(){
		return $this->page;
	}
}
?>