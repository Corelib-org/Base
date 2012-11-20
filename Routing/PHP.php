<?php
namespace Corelib\Routing;

class PHP extends ArrayRegistry {

	public function __construct($filename){
		assert('is_file($filename)');
		include($filename);
		parent::__construct($pages);
	}

}