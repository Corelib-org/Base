<?php
class CodeGeneratorModel extends CodeGeneratorPlugin {
	public function init(){
		$this->_addFile(new CodeGeneratorModelFile($this->getPath(), $this->getClassName(), $this->getTable(), $this->fields));
		$this->_addFile(new CodeGeneratorModelFileDAOMySQLi($this->getPath(), $this->getClassName(), $this->getTable(), $this->fields));
	}
}
?>