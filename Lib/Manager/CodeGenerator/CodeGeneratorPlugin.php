<?php
abstract class CodeGeneratorPlugin implements Output {
	private $classname = null;
	private $path = null;
	private $table = null;
	
	private $files = array();
	
	private $write_result = null;
	
	protected $content = null;
	protected $fields = null;
	
	public function __construct($classname, $class){
		$this->path = $class['path'];
		$this->table = $class['table'];
		$this->classname = $classname;
		$this->fields = &$class['fields'];
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
	
	protected function _addFile(CodeGeneratorFile $file){
		$this->files[] = $file;
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
		/*
		echo '<pre>';
		print_r($this->files);
		echo '</pre>';
		exit;
		*/
		$files = $xml->createElement('files');
		foreach ($this->files as $file){
			$files->appendChild($file->getXML($xml));
		}
		return $files;
	}
	
}
?>