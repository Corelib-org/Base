<?php
namespace Corelib\Base\PageFactory\Toolbar;
use Corelib\Base\Event\Action, Corelib\Base\Event\Event;

class Render extends Action {

	private $toolbar = null;

	public function __construct(Toolbar $toolbar){
		$this->toolbar = $toolbar;
	}

	public function update(Event $event){
		$this->toolbar->inject($event->getPage());
	}

}
?>