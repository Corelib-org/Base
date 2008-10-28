<?php
class WebPage extends ManagerPage {
	public function build(){
		$input = InputHandler::getInstance();
		
		$this->xsl->addTemplate('Base/Share/Resources/XSLT/Pages/generator.xsl');
		$this->addContent(ManagerConfig::getInstance()->getPropertyOutput('codewriter'));
		
		if($input->validateGet('name', new InputValidatorNotEmpty())){
			if($input->getGet('name') == 'ALL'){
				$generator = new CodeGenerator(ManagerConfig::getInstance()->getPropertyXML('codewriter'));
			} else {
				$generator = new CodeGenerator(ManagerConfig::getInstance()->getPropertyXML('codewriter'), $input->getGet('name'));
			}
			if($input->validateGet('write', new InputValidatorEquals('true'))){
				$generator->applyChanges();
			}
			$this->addContent($generator);
		}
	}
}
?>