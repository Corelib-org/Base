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
			$actions = $codewriter->appendChild($xml->createElement('class'));
			$actions->setAttribute('name', $classname);
			$actions->setAttribute('table', $classinfo['table']);
			foreach ($classinfo['generators'] as $generator){
				$actions->appendChild($generator->getXML($xml));
			}
		}
		return $codewriter;
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
		
		foreach ($this->classes as $classname => $class){
			$this->classes[$classname]['fields'] = $this->_lookupClassNames($class['fields']);
			foreach ($class['generators'] as $key => $generator){
				$generator = new $generator[0]($classname, $this->classes[$classname], $generator[1]);
				$generator->init();	
				$this->classes[$classname]['generators'][$key] = $generator;
			}
		}
	}
	
	private function _lookupClassNames($fields){
		foreach ($fields as $key => $field){
			if(!isset($fields[$key]['class'])){
				if(!preg_match('/^pk_/', $field['field'])){
					$fields[$key]['class'] = CodeGeneratorClassResolver::getInstance()->getClass($this->_convertFerignKeyToKey($field['field']));
				} else {
					$fields[$key]['class'] = false;
				}
			}
		}
		return $fields;
	}
	
	private function _loadClass(DOMElement $class, $path = null){
		$classname = $class->getAttribute('name');
		if($class->parentNode->nodeName == 'subclasses'){
			$this->classes[$classname]['subclass'] = $class->parentNode->parentNode->getAttribute('name');
		} else {
			$this->classes[$classname]['subclass'] = false;
		}
		
		$this->classes[$classname]['table'] = $class->getAttribute('table');
		if($class->getAttribute('analyse') != 'false'){
			$this->classes[$classname]['fields'] = $this->dao->analyseTable($class->getAttribute('table'));
		} else {
			$this->classes[$classname]['fields'] = array();
		}
		
		// $this->_generateFieldConstants($classname);
		
		if(!preg_match('/[syQ]$/', $classname)){
			$foldername = $classname.'s';
		} else {
			$foldername = $classname;
		}
		if(!$class->getAttribute('path')){
			$this->classes[$classname]['path'] = Manager::parseConstantTags($path.'Lib/'.$foldername.'/');
		} else {
			$this->classes[$classname]['path'] = Manager::parseConstantTags($class->getAttribute('path').$foldername.'/');
		}
		
		$xpath = new DOMXPath($class->ownerDocument);
		$generators = $xpath->query('generators/generator', $class);
		$this->classes[$classname]['generators'] = array();
		for ($i = 0; $i < $generators->length; $i++){
			$class = $generators->item($i)->getAttribute('name');
  			$this->classes[$classname]['generators'][] = array($class, $generators->item($i));
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
	private function _convertFerignKeyToKey($field){
		return preg_replace('/^fk_/', 'pk_', $field);
	}
	
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('CodeGenerator');
		}
		return true;
	}
}
?>