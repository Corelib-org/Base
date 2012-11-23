<?php
namespace Corelib\Base\Routing;

class PHP extends ArrayRegistry {

	public function __construct($filename){
		assert('is_file($filename)');
		parent::__construct(realpath($filename));

		if(!$this->isCached()){
			include($filename);
			$this->load($pages);
		}

	}

}