<?php
abstract class CodeGeneratorPlugin implements Output {
	private $classname = null;
	private $path = null;
	private $table = null;
	
	private $files = array();
	
	private $write_result = null;
	
	protected $settings = null;
	
	protected $content = null;
	protected $fields = null;
	
	public function __construct($classname, $class, $settings){
		$this->path = $class['path'];
		$this->table = $class['table'];
		$this->classname = $classname;
		$this->fields = $class['fields'];
		$this->settings = $settings;
	}
	
	public function generate(){
		foreach ($this->files as $file){
			$file->generate();	
		}
	}
	
	public function write(){
		foreach ($this->files as $file){
			$file->write();	
		}
	}
	
	abstract public function init();
	
	protected function _createFileInstance($class){
		return new $class($this->getPath(), $this->getClassName(), $this->getTable(), $this->fields);
	}
	protected function _addFile(CodeGeneratorFile $file){
		$this->files[] = $file;	
		return $file;
	}
	
	public function getClassName(){
		return $this->classname;
	}
	public function getPath(){
		return $this->path;
	}
	public function getTable(){
		return $this->table;
	}
	
	public function getXML(DOMDocument $xml){
		$files = $xml->createElement('files');
		foreach ($this->files as $file){
			$files->appendChild($file->getXML($xml));
		}
		return $files;
	}
	
}
?>