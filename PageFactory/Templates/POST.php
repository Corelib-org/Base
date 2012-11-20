<?php
namespace Corelib\Base\PageFactory\Templates;

/**
 * Page factory post engine class.
 *
 * This template is supposed to be used to handle post requests.
 * and therefore it is a no output template engine.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class POST extends HTTP {
	public function prepare(){
		parent::prepare();
		return false;
	}
	public function render(){
		return false;

	}

	public function addContent($content){
		return $content;
	}
	public function addSettings($settings){
		return $settings;
	}

}