<?php
class CodeGeneratorModelList extends CodeGeneratorPlugin {
	public function init(){
		$this->_addFile($this->_createFileInstance('CodeGeneratorModelListFile'));
		$this->_addFile($this->_createFileInstance('CodeGeneratorModelListFileDAOMySQLi'));
	}
}
?>