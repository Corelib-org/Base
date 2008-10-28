<?php
class CodeGeneratorModelDAOMySQLi extends CodeGeneratorPlugin {
	public function __construct($classname, $class){
		$class['path'] .= 'Lib/DAO/';
		parent::__construct($classname, $class);
		$this->_setFilename('MySQLi.'.$classname.'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelDAOMySQLi.php');
	}	
	
	public function generate(){
		$this->_writeSelectColumns($this->content);
		$this->_writeSpecialCreateFields($this->content);
		$this->_writeSpecialUpdateFields($this->content);
		$this->_writeUtilityMethods($this->content);
		$this->_writeTableName($this->content);
		$this->_writeClassName($this->content);
	}
	
	public function _writeSelectColumns(&$content){
		foreach ($this->fields as $field){
			$field['field'] = '`'.$field['field'].'`';
			if(strstr($field['smarttype'], 'timestamp')){
				$field['field'] = 'UNIX_TIMESTAMP('.$field['field'].') AS '.$field['field'];
			}
			if(strstr($field['datatype'], 'boolean')){
				$field['field'] = 'IF('.$field['field'].'=\\\'TRUE\\\', true, false) as '.$field['field'];
			}
			$select_colums[] =  $field['field'];
		}
		$content = str_replace('${selectcolumns}', implode(",\n\t                        ", $select_colums), $content);
	}
	private function _writeSpecialCreateFields(&$content){
		$conditions = array();
		if($block = $this->_getCodeBlock($content, 'Special create fields')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_condition = false;
			foreach ($lines as $key => $line){
				if(preg_match('/if.*?'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = $condition[1];
				} else if($in_condition !== false){
					$conditions[$in_condition] .= $line."\n";
					if(preg_match('/^\t\t\}/', $line)){
						$in_condition = false;
					}
				} else if(preg_match('/'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = $condition[1];
				}
			}
						
			foreach ($this->fields as $field){
				if(!isset($conditions[$field['constant']])){
					switch ($field['smarttype']){
						case 'on_create_current_timestamp':
							$conditions[$field['constant']] = "\t\t".'$data->setSpecialValue('.$this->_getClassName().'::'.$field['constant'].', \'NOW()\');';
							break;
						case 'timestamp':
							if($field['readonly'] !== true){
								$code  = "\t\t".'if($data->isChanged('.$this->_getClassName().'::'.$field['constant'].')){'."\n";
								$code .= "\t\t\t".'$data->setSpecialValue('.$this->_getClassName().'::'.$field['constant'].', \'FROM_UNIXTIME(?)\');'."\n";
								$code .= "\t\t".'}';
								$conditions[$field['constant']] = $code;
							}
							break;							
					}
					if(isset($field['unique']) && $field['unique'] === true){
						$code = $this->_writeUniqueIfLoop($field);
						if(isset($conditions[$field['constant']])){
							$conditions[$field['constant']] .= "\n".$code;
						} else {
							$conditions[$field['constant']] = $code;
						}
					}
				}
			}
			$condition_code = '';
			foreach ($conditions as $condition){
				$condition_code .= rtrim($condition)."\n";
			}			
			$this->_writeCodeBlock($content, 'Special create fields', $condition_code);
		}
	}
	private function _writeSpecialUpdateFields(&$content){
		$conditions = array();
		if($block = $this->_getCodeBlock($content, 'Special update fields')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_condition = false;
			foreach ($lines as $key => $line){
				if(preg_match('/if.*?'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = $condition[1];
				} else if($in_condition !== false){
					$conditions[$in_condition] .= $line."\n";
					if(preg_match('/^\t\t\}/', $line)){
						$in_condition = false;
					}
				} else if(preg_match('/'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = $condition[1];
				}
			}
			foreach ($this->fields as $field){
				if(!isset($conditions[$field['constant']])){
					if(strstr($field['smarttype'], 'timestamp')){
						if($field['readonly'] !== true){
							$code  = "\t\t".'if($data->isChanged('.$this->_getClassName().'::'.$field['constant'].')){'."\n";
							$code .= "\t\t\t".'$data->setSpecialValue('.$this->_getClassName().'::'.$field['constant'].', \'FROM_UNIXTIME(?)\');'."\n";
							$code .= "\t\t".'}';
							$conditions[$field['constant']] = $code;
						}
					}
					if(isset($field['unique']) && $field['unique'] === true){
						$code = $this->_writeUniqueIfLoop($field, '$id');
						if(isset($conditions[$field['constant']])){
							$conditions[$field['constant']] .= "\n".$code;
						} else {
							$conditions[$field['constant']] = $code;
						}
					}
				}
			}
			$condition_code = '';
			foreach ($conditions as $condition){
				$condition_code .= rtrim($condition)."\n";
			}			
			$this->_writeCodeBlock($content, 'Special update fields', $condition_code);
		}
	}	

	private function _writeUniqueIfLoop($field, $id='null'){
		$code  = "\t\t".'if($data->isChanged('.$this->_getClassName().'::'.$field['constant'].') && !$this->'.$this->_createMethodName('is', $field['property']).'Available('.$id.', $data->get('.$this->_getClassName().'::'.$field['constant'].'))){'."\n";
		$code .= "\t\t\t".'return false;'."\n";
		$code .= "\t\t".'}';
		return $code;
	}
	
	private function _writeUtilityMethods(&$content){
		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Utility methods')){
			
			$lines = preg_split("/(\n|\r)/", $block);
			$in_method = false;
			foreach ($lines as $key => $line){
				if(preg_match('/function\s*(.*?)\s*\(/', $line, $method)){
					$methods[$method[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_method = $method[1];
				} else if($in_method !== false){
					$methods[$in_method] .= $line."\n";
				}
			}
			
			
			foreach ($this->fields as $field){
				if(!isset($field['unique']) || $field['unique'] === true){
					$method = $this->_createMethodName('is', $field['property']).'Available';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Check if '.$field['property'].' is available'."\n";
					$docblock .= "\t".' *'."\n";
					$docblock .= "\t".' * @param '.$field['datatype'].' '.$field['property']."\n";
					$docblock .= "\t".' * @return boolean true on success, else return false'."\n";
					$docblock .= "\t".' */'."\n";
						
					$code  = '$query = \'SELECT `\'.'.$this->_getClassName().'::FIELD_ID.\'`'."\n";
					$code .= '          FROM `${tablename}'."`\n";
					$code .= '          WHERE `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=\\\'\'.mysql_escape_string($'.$field['property'].').\'\\\'\';'."\n";
					$code .= 'if(!is_null($id)){'."\n";
					$code .= '	$query .= \' AND `\'.'.$this->_getClassName().'::FIELD_ID.\'` != \\\'\'.$id.\'\\\'\';'."\n";
					$code .= '}'."\n";
					$code .= '$query = $this->slaveQuery(new MySQLiQuery($query));'."\n";
					$code .= 'if($query->getNumRows() > 0){'."\n";
					$code .= '	return false;'."\n";
					$code .= '} else {'."\n";
					$code .= '	return true;'."\n";
					$code .= '}';
					
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id=null, $'.$field['property']);
					}
				}
			}
			$method_code = '';
			
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Utility methods', $method_code);			
		}
	}	
}
?>