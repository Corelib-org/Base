<?php
class WebPage extends ManagerPage {
	public function build(){

	}

	public function database(){
		$input = InputHandler::getInstance();
		$input->validatePost('exclude', new InputValidatorArray(new InputValidatorNotEmpty()));
		
		$db = new DatabaseTool();
		if($input->isValidPost('exclude')){
			call_user_func_array(array($db, 'setExcludes'), array_keys($input->getPost('exclude')));
		}
		$db->update();
		$this->post->setLocation('corelib/system/database/');
	}
}
?>