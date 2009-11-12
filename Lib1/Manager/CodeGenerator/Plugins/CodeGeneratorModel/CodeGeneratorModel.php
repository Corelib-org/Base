<?php
class CodeGeneratorModel extends CodeGeneratorPlugin {
	public function init(){
		$file = $this->_addFile($this->_createFileInstance('CodeGeneratorModelFile'));
		$dao = $this->_addFile($this->_createFileInstance('CodeGeneratorModelFileDAOMySQLi'));
		
		$content = $this->settings->getElementsByTagName('content');
		$analyser = Database::getDAO('CodeGenerator');
		for ($i = 0; $i < $content->length; $i++){
			if($table = $content->item($i)->getAttribute('table')){
				$data = $analyser->analyseTable($table);
				$file->addContentTable($table, $data);
				$dao->addContentTable($table, $data);
			}
		}
	}
}
?>