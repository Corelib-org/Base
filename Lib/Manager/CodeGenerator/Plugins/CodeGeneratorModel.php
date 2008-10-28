<?php
class CodeGeneratorModel extends CodeGeneratorPlugin {
	public function __construct($classname, $class){
		parent::__construct($classname, $class);
		$this->_setFilename($classname.'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Model.php');
	}
		
	public function generate(){
		$this->_writeClassName($this->content);
		$this->_writeClassVar($this->content);
		$this->_writeFieldConstants($this->content);
		$this->_writeProperties($this->content);
		$this->_writeGetters($this->content);
		$this->_writeSetters($this->content);
		$this->_writeConverters($this->content);
		$this->_writeUtilityMethods($this->content);
		$this->_writeArrayReader($this->content);
		$this->_writeEnumConstants($this->content);
		$this->_writeXMLOutput($this->content);
	}
	
	private function _writeFieldConstants(&$content){
		$constants = array();
		if($block = $this->_getCodeBlock($content, 'Field constants')){
			preg_match_all('/const\s*(.*?)\s*=\s*[\'"](.*?)[\'"];/', $block, $matches);
			foreach ($matches[1] as $key => $constant){
				$constants[$constant] = $matches[2][$key];
			}
			foreach ($this->fields as $field){
				if(!isset($constants[$field['constant']])){
					$constants[$field['constant']] = $field['field'];
				}
			}
			$constant_code = '';
			foreach ($constants as $constant => $value){
				$constant_code .= "\t".'const '.$constant.' = \''.$value.'\';'."\n";
			}
			$this->_writeCodeBlock($content, 'Field constants', $constant_code);
		}
	}
	private function _writeProperties(&$content){
		$properties = array();
		if($block = $this->_getCodeBlock($content, 'Properties')){
			preg_match_all('/private\s*\$(.*?)\s*=\s*[\'"]*(.*?)[\'"]*;/', $block, $matches);
			foreach ($matches[1] as $key => $property){
				$properties[$property] = $matches[2][$key];
			}
			foreach ($this->fields as $field){
				if(!isset($properties[$field['property']])){
					$value = 'null';
					if(isset($field['default']) && $field['default'] === true){
						$value = 'true';
					} else if(isset($field['default']) && $field['default'] === false){
						$value = 'false';
					}
					$properties[$field['property']] = $value;
				}
			}
			$property_code = '';
			foreach ($properties as $property => $value){
				$property_code .= "\t".'private $'.$property.' = '.$value.';'."\n";
			}
			$this->_writeCodeBlock($content, 'Properties', $property_code);
		}
	}
	private function _writeGetters(&$content){
		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Getter methods')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_method = false;
			foreach ($lines as $key => $line){
				if(preg_match('/function\s*(.*?)\s*\(/', $line, $method)){
					$methods[$method[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_method = $method[1];
				} else if($in_method !== false){
					$methods[$in_method] .= $line."\n";
					if(preg_match('/^\t\}/', $line) && isset($lines[($key + 1)]) && preg_match('/\t\/\*\*/', $lines[($key + 1)])){
						$in_method = false;
					}
				}
			}
			foreach ($this->fields as $field){
				$method = $this->_createMethodName('get', $field['property']);
				if(!isset($methods[$method])){
					if(isset($field['class'])){
						$type = $field['class'];
					} else {
						$type = $field['datatype'];
					}
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Get '.$field['property']."\n";
					$docblock .= "\t".' *'."\n";
					$docblock .= "\t".' * @return '.$type."\n";
					$docblock .= "\t".' */'."\n";
					
					if(isset($field['converter']) && $field['converter'] === true){
						$docblock  = '/**'."\n";
						$docblock .= "\t".' * Get '.$field['property']."\n";
						$docblock .= "\t".' *'."\n";
						$docblock .= "\t".' * @return mixed'."\n";
						$docblock .= "\t".' */'."\n";
						
						$code  = 'if(!is_null($this->'.$field['property'].'_converter)){'."\n";
						$code .= '	return $this->'.$field['property'].'_converter->convert($this->'.$field['property'].');'."\n";
						$code .= '} else {'."\n";
						$code .= '	return $this->'.$field['property'].';'."\n";
						$code .= '}';
						$methods[$method] = $this->_makeMethod($method, $code, $docblock);
					} else {
						$methods[$method] = $this->_makeMethod($method, 'return $this->'.$field['property'].';', $docblock);
					}
				}
			}
			$method_code = '';
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Getter methods', $method_code);
		}
	}
	private function _writeSetters(&$content){
		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Setter methods')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_method = false;
			foreach ($lines as $key => $line){
				if(preg_match('/function\s*(.*?)\s*\(/', $line, $method)){
					$methods[$method[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_method = $method[1];
				} else if($in_method !== false){
					$methods[$in_method] .= $line."\n";
					if(preg_match('/^\t\}/', $line) && isset($lines[($key + 1)]) && preg_match('/\t\/\*\*/', $lines[($key + 1)])){
						$in_method = false;
					}
				}
			}
			foreach ($this->fields as $field){
				if(!isset($field['readonly']) || $field['readonly'] !== true){
					$method = $this->_createMethodName('set', $field['property']);
					$param = '';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Set '.$field['property']."\n";
					$docblock .= "\t".' *'."\n";
					$docblock .= "\t".' * @param '.$field['datatype'].' '.$field['property']."\n";
					$docblock .= "\t".' * @return boolean true on success, else return false'."\n";
					$docblock .= "\t".' */'."\n";
					
					$codeleft = '';
					$coderight = '';
					$indent = '';
					if(isset($field['unique']) && $field['unique'] === true){
						$codeleft  = '$this->_getDAO();'."\n";
						$codeleft .= 'if($this->dao->isUsernameAvailable($this->id, $'.$field['property'].')){'."\n";
						
						$indent .= "\t";
						
						$coderight .= "\n".'} else {'."\n";
						$coderight .= "\t".'return false;'."\n";
						$coderight .= '}';
					}
					

					$code = $indent.'$this->'.$field['property'].' = $'.$field['property'].';'."\n";
					if(isset($field['class'])){
						$param .= $field['class'].' ';
						$code .= $indent.'$this->datahandler->set(self::'.$field['constant'].', $'.$field['property'].'->getID());'."\n";
					} else {
						$code .= $indent.'$this->datahandler->set(self::'.$field['constant'].', $'.$field['property'].');'."\n";
					}
					$code .= $indent.'return true;';
					$param .= '$'.$field['property'];
					if(isset($field['values'])){
						$param .= '=self::'.$this->_makeEnumConstantName($field['property'], $field['default']);
					} else if(isset($field['default']) && $field['default'] == 'NULL'){
						$param .= '=null';
					}
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $codeleft.$code.$coderight, $docblock, $param);
					}
				}
			}
			$method_code = '';
			
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Setter methods', $method_code);			
		}
	}
	protected function _writeConverters(&$content){
		$properties = array();
		if($block = $this->_getCodeBlock($content, 'Converter properties')){
			preg_match_all('/private\s*\$(.*?)\s*=\s*[\'"]*(.*?)[\'"]*;/', $block, $matches);
			foreach ($matches[1] as $key => $property){
				$properties[$property] = $matches[2][$key];
			}
			foreach ($this->fields as $field){
				$field['property'] = $field['property'].'_converter';
				if(!isset($properties[$field['property']]) && (isset($field['converter']) && $field['converter'] === true)) {
					$value = 'null';
					if(isset($field['default']) && $field['default'] === true){
						$value = 'true';
					} else if(isset($field['default']) && $field['default'] === false){
						$value = 'false';
					}
					$properties[$field['property']] = $value;
				}
			}
			$property_code = '';
			foreach ($properties as $property => $value){
				$property_code .= "\t".'private $'.$property.' = '.$value.';'."\n";
			}
			$this->_writeCodeBlock($content, 'Converter properties', $property_code);
		}
		
		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Converter methods')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_method = false;
			foreach ($lines as $key => $line){
				if(preg_match('/function\s*(.*?)\s*\(/', $line, $method)){
					$methods[$method[1]] = rtrim($line)."\n";
					$in_method = $method[1];
				} else if($in_method !== false){
					$methods[$in_method] .= $line."\n";
					if(preg_match('/^\t\}/', $line) && isset($lines[($key + 1)]) && preg_match('/\t\/\*\*/', $lines[($key + 1)])){
						$in_method = false;
					}					
				}
			}
			foreach ($this->fields as $field){
				$field['property'] = $field['property'].'_converter';
				$method = $this->_createMethodName('set', $field['property']);
				if(!isset($methods[$method]) && (isset($field['converter']) && $field['converter'] === true)){
					$code = '$this->'.$field['property'].' = $converter;';
					$methods[$method] = $this->_makeMethod($method, $code, null, 'Converter $converter');
				}
			}
			$method_code = '';
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Converter methods', $method_code);			
		}		
	}
	private function _writeArrayReader(&$content){
		if($block = $this->_getCodeBlock($content, 'setFromArray method content')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_loop = false;
			$loops = array();
			foreach ($lines as $key => $line){
				if(preg_match('/if\(.*?\(\$array\[self::(.*?)\]\)\)\{/', $line, $loop)){
					$loops[$loop[1]] = rtrim($line)."\n";
					$in_loop = $loop[1];
				} else if($in_loop !== false){
					$loops[$in_loop] .= $line."\n";
				}
			}

			foreach ($this->fields as $field){
				if(!isset($loops[$field['constant']])){
					$code  = "\t\t".'if(isset($array[self::'.$field['constant'].'])){'."\n";
					if(isset($field['class'])){
						$code .= "\t\t\t".'$this->'.$field['property'].' = new '.$field['class'].'(('.$field['datatype'].') $array[self::'.$field['constant'].']);'."\n"; 
					} else {
						$code .= "\t\t\t".'$this->'.$field['property'].' = ('.$field['datatype'].') $array[self::'.$field['constant'].'];'."\n";
					}
					
					$code .= "\t\t".'}';
					$loops[$field['constant']] = $code;
				}
			}
			$loop_code = '';
			foreach ($loops as $loop){
				$loop_code .= rtrim($loop)."\n";
			}			
			$this->_writeCodeBlock($content, 'setFromArray method content', $loop_code);
		}
		
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
					$method = $this->_createMethodName('getBy', $field['property']);
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Get data by '.$field['property']."\n";
					$docblock .= "\t".' *'."\n";
					$docblock .= "\t".' * @param '.$field['datatype'].' '.$field['property']."\n";
					$docblock .= "\t".' * @return boolean true on success, else return false'."\n";
					$docblock .= "\t".' */'."\n";
						
					$code  = '$this->_getDAO(false);'."\n";
					$code .= '$this->_setFromArray($this->dao->'.$this->_createMethodName('getBy', $field['property']).'($'.$field['property'].'));'."\n";
					$code .= 'if(is_null($this->id)){'."\n";
					$code .= '	return false;'."\n";
					$code .= '} else {'."\n";
					$code .= '	return true;'."\n";
					$code .= '}';
	
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$'.$field['property']);
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
	
	private function _writeXMLOutput(&$content){
		if($block = $this->_getCodeBlock($content, 'Get XML method')){
			
			$lines = preg_split("/(\n|\r)/", $block);
			$in_property = false;
			$properties = array();
			
			foreach ($lines as $key => $line){
				if(preg_match('/if\(.*?\(\$this->(.*?)\)\)\{/', $line, $property)){
					$properties[$property[1]] = rtrim($line)."\n";
					$in_property = $property[1];
				} else if($in_property !== false){
					$properties[$in_property] .= $line."\n";
				}
			}
			
			foreach ($this->fields as $field){
				if(!isset($properties[$field['property']])){
					$code  = "\t\t".'if(!is_null($this->'.$field['property'].')){'."\n";
					if($field['datatype'] == 'boolean'){
						$code .= "\t\t\t".'if($this->'.$field['property'].'){'."\n";
						$code .= "\t\t\t\t".'$'.$field['property'].' = \'true\';'."\n";
						$code .= "\t\t\t".'} else {'."\n";
						$code .= "\t\t\t\t".'$'.$field['property'].' = \'false\';'."\n";
						$code .= "\t\t\t".'}'."\n";
						$code .= "\t\t\t".'$'.$this->_getClassVar().'->setAttribute(\''.$field['property'].'\', $'.$field['property'].');'."\n";
					} else if($field['smarttype'] == 'text' || $field['smarttype'] == 'blob'){
						$code .= "\t\t\t".'$'.$this->_getClassVar().'->appendChild($xml->createElement(\''.$field['property'].'\', $this->'.$this->_createMethodName('get', $field['property']).'()));'."\n";
					} else {
						if(isset($field['class'])){
							$code .= "\t\t\t".'$'.$this->_getClassVar().'->setAttribute(\''.$field['property'].'\', $this->'.$field['property'].'->getID());'."\n"; 
						} else {
							$code .= "\t\t\t".'$'.$this->_getClassVar().'->setAttribute(\''.$field['property'].'\', $this->'.$this->_createMethodName('get', $field['property']).'());'."\n";
						}
						
					}
					$code .= "\t\t".'}';
					$properties[$field['constant']] = $code;
				}
			}
			$property_code = '';
			foreach ($properties as $property){
				$property_code .= rtrim($property)."\n";
			}			
			$this->_writeCodeBlock($content, 'Get XML method', $property_code);
		}
		
	}	
	
	private function _writeEnumConstants(&$content){
		if($block = $this->_getCodeBlock($content, 'Enum constants')){
			$constants = array();
			preg_match_all('/const\s*(.*?)\s*=/', $block, $matches);
			foreach ($matches[1] as $key => $constant){
				$constants[$constant] = $matches[1][$key];
			}
			foreach ($this->fields as $field){
				if(isset($field['values']) && is_array($field['values'])){
					foreach ($field['values'] as $value){
						$constant = $this->_makeEnumConstantName($field['property'], $value);
						if(!isset($constants[$value])){
							$constants[$constant] = $value;
						}
					}
				}
			}
			$constant_code = '';
			foreach ($constants as $constant => $value){
				$constant_code .= "\t".'const '.$constant.' = \''.$value.'\';'."\n";
			}
			$this->_writeCodeBlock($content, 'Enum constants', $constant_code);			
		}
	}
	protected function _makeEnumConstantName($field, $var){
		return strtoupper($field.'_'.$var);
	}	
}
?>