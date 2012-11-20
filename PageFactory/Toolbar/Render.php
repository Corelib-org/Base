<?php
namespace Corelib\Base\PageFactory\Toolbar;

class Render extends \EventAction {

	private $toolbar = null;

	public function __construct(Toolbar $toolbar){
		$this->toolbar = $toolbar;
	}

	public function update(\Event $event){
		$this->toolbar->inject($event->getPage());
	}

}
?>