<?php
interface DAO_CodeGenerator {
	public function analyseTable($table);
}

class CodeGenerator implements Output {
	/**
	 * @var DAO_CodeGenerator
	 */
	private $dao = null;
	
	/**
	 * @var GeneratorClassResolver
	 */
	private $resolver = null;
	
	private $classes = array();
	
	public function __construct(DOMElement $tree, $class=null){
		$this->_getDAO();
		$this->resolver = CodeGeneratorClassResolver::getInstance();
		$this->_loadClassTree($tree, $class);
		$this->_generateCode();
	}

	public function applyChanges(){
		foreach ($this->classes as $classname => $classinfo){
			foreach ($classinfo['generators'] as $generator){
				$generator->write();
			}
		}
	}
	
	public function getXML(DOMDocument $xml){
		$codewriter = $xml->createElement('codewriter');
		foreach ($this->classes as $classname => $classinfo){
			$actions = $xml->createElement('actions');
			$actions->setAttribute('name', $classname);
			$actions->setAttribute('table', $classinfo['table']);
			foreach ($classinfo['generators'] as $generator){
				$actions->appendChild($generator->getXML($xml));
				$codewriter->appendChild($actions);
			}
		}
		return $codewriter;
	}
		
	public function &getArray(){
		
	}
	
	public function _generateCode(){
		foreach ($this->classes as $classname => $classinfo){
			foreach ($classinfo['generators'] as $generator){
				$generator->generate();
			}
		}
	}
	
	private function _loadClassTree(DOMElement $tree, $class=null){
		$xpath = new DOMXPath($tree->ownerDocument);
		
		if(!is_null($class)){
			$classes = $xpath->query('class[@name = \''.$class.'\']', $tree);
		} else {
			$classes = $xpath->query('class', $tree);
		}
		
		for ($i = 0; $i < $classes->length; $i++){
			$classname = $this->_loadClass($classes->item($i));
			$subclasses = $xpath->query('subclasses/class', $classes->item($i));
			for ($si = 0; $si < $subclasses->length; $si++){
				$this->_loadClass($subclasses->item($si), $this->classes[$classname]['path']);
			}
		}
	}
	
	private function _loadClass(DOMElement $class, $path = null){
		$classname = $class->getAttribute('name');

		$this->classes[$classname]['table'] = $class->getAttribute('table');
		$this->classes[$classname]['fields'] = $this->dao->analyseTable($class->getAttribute('table'));

		$this->_generateFieldConstants($classname);
		
		if(!preg_match('/s$/', $classname)){
			$foldername = $classname.'s';
		} 
		if(!$class->getAttribute('path')){
			$this->classes[$classname]['path'] = $path.'lib/'.$foldername.'/';
		} else {
			$this->classes[$classname]['path'] = $class->getAttribute('path').$foldername.'/';
		}
		
		$xpath = new DOMXPath($class->ownerDocument);
		$generators = $xpath->query('generators/generator', $class);
		for ($i = 0; $i < $generators->length; $i++){
			$class = $generators->item($i)->getAttribute('name');
  			$this->classes[$classname]['generators'][] = new $class($classname, $this->classes[$classname], $generators->item($i));
		}
		$this->resolver->addClass($this->_convertTableToKey($this->classes[$classname]['table']), $classname);
		
		return $classname;
	}

	private function _generateFieldConstants($classname){
		foreach ($this->classes[$classname]['fields'] as $key => $field){
			$table_name = $this->classes[$classname]['table'];
			
			if($field['field'] == $this->_convertTableToKey($table_name)){
				$const = 'FIELD_ID';
				$property = 'id';
				$field['readonly'] = true;
			} else if(preg_match('/^fk_/', $field['field'])){
				$const = preg_replace('/^fk(_'.$table_name.')*_(.*?)/', '\\3', $field['field']);
				$const = preg_replace('/s$/', '', $const);
				$property = $const;
				$const = 'FIELD_'.$const.'_ID';
				
				$class = preg_replace('/^fk_/', 'pk_', $field['field']);
				if(preg_match('/_(.*?)_parent/', $class, $match)){
					$class = 'pk_'.$match[1];
				}
				
				$field['class'] = $this->resolver->getClass($class);
				//$const = $out['Field'];
			} else {
				$const = 'FIELD_'.$field['field'];
				$property = strtolower($field['field']);
			}
			$field['constant'] = strtoupper($const);
			$field['property'] = $property;
			$this->classes[$classname]['fields'][$key] = $field;
		}
	}
	
	private function _convertTableToKey($table){
		return preg_replace('/^tbl_/', 'pk_', $table);
	}
	
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('CodeGenerator');
		}
		return true;
	}
}
?>