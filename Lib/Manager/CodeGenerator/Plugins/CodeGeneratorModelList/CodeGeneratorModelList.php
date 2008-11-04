<?php
class CodeGeneratorModelList extends CodeGeneratorPlugin {
	public function init(){
		$this->_addFile(new CodeGeneratorModelListFile($this->getPath(), $this->getClassName(), $this->getTable(), $this->fields));
		$this->_addFile(new CodeGeneratorModelListFileDAOMySQLi($this->getPath(), $this->getClassName(), $this->getTable(), $this->fields));
	}
}
?>