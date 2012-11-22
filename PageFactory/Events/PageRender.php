<?php
namespace Corelib\Base\PageFactory\Events;
use Corelib\Base\Event\Event;

class PageRender implements Event {

	private $page = null;

	public function __construct(&$page){
		$this->page = &$page;
	}

	public function &getPage(){
		return $this->page;
	}

}