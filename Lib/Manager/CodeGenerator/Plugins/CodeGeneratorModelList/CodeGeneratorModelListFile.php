<?php
class CodeGeneratorModelListFile extends CodeGeneratorModelFile {
	public function __construct($path, $classname, $table, $fields){
		$path .= 'Lib/';
		parent::__construct($path, $classname, $table, $fields);
		
		$this->_setFilename($classname.'List.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelList.php');
	}
		
	public function generate(){
		$this->_writeClassName($this->content);
		$this->_writeClassVar($this->content);
		$this->_writeConverters($this->content);
		$this->_writeOrders($this->content);
		$this->_writeFilters($this->content);
		$this->_writeListElement($this->content);
	}	
	
	
	protected function _writeConverters(&$content){
		parent::_writeConverters($content);
		if($block = $this->_getCodeBlock($content, 'Set converters')){
			$lines = preg_split("/(\n|\r)/", $block);
			$loops = array();
			$in_loop = false;
			foreach ($lines as $key => $line){
				if(preg_match('/if\(.*?\$this-\>(.*?)\).*?\{/', $line, $loop)){
					$loops[$loop[1]] = rtrim($line)."\n";
					$in_loop = $loop[1];
				} else if($in_loop !== false){
					$loops[$in_loop] .= $line."\n";
				}
			}
			foreach ($this->fields as $field){
				$field['property'] = $field['property'].'_converter';
				if(!isset($loops[$field['property']]) && (isset($field['converter']) && $field['converter'] === true)){
					$code  = "\t\t\t\t".'if(!is_null($this->'.$field['property'].')){'."\n";
					$code .= "\t\t\t\t\t".'$item->'.$this->_createMethodName('set', $field['property']).'($this->'.$field['property'].');'."\n";
					$code .= "\t\t\t\t".'}';
					$loops[$field['property']] = $code;
				}
			}
			$loops_code = '';
			foreach ($loops as $loop){
				$loops_code .= rtrim($loop)."\n";
			}
			$this->_writeCodeBlock($content, 'Set converters', $loops_code);
			
		}
		
	}
	protected function _writeOrders(&$content){
		$properties = array();

		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Order methods')){
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
				$method = $this->_createMethodName('set', $field['property'].'OrderDesc');
				if($field['constant'] != 'FIELD_ID'){
					if(!isset($methods[$method]) && (isset($field['sortable']) && $field['sortable'] === true)){
						$docblock  = '/**'."\n";
						$docblock .= "\t".' * Order '.$field['property'].' descending'."\n";
						$docblock .= "\t".' */'."\n";					
						
						$code = '$this->order->set('.$this->_getClassName().'::'.$field['constant'].', DATABASE_ORDER_DESC);';
						$methods[$method] = $this->_makeMethod($method, $code, $docblock);
					}
					
					$method = $this->_createMethodName('set', $field['property'].'OrderAsc');
					if(!isset($methods[$method]) && (isset($field['sortable']) && $field['sortable'] === true)){
						$docblock  = '/**'."\n";
						$docblock .= "\t".' * Order '.$field['property'].' ascending'."\n";
						$docblock .= "\t".' */'."\n";					
						
						$code = '$this->order->set('.$this->_getClassName().'::'.$field['constant'].', DATABASE_ORDER_ASC);';
						$methods[$method] = $this->_makeMethod($method, $code, $docblock);
					}
				}
			}
			$method_code = '';
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Order methods', $method_code);			
		}		
	}	
	protected function _writeFilters(&$content){

		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Filter methods')){
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
				$method = $this->_createMethodName('set', $field['property'].'Filter');
				if($field['constant'] != 'FIELD_ID' && !isset($methods[$method]) && (isset($field['sortable']) && $field['sortable'] === true)){
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Filter on '.$field['property']."\n";
					$docblock .= "\t".' *'."\n";
										
					$param = '';
					if(strstr($field['smarttype'], 'timestamp')){
						$param .= '$after=null, $before=null';

						$docblock .= "\t".' * @param integer $after Unixtimestamp'."\n";
						$docblock .= "\t".' * @param integer $before Unixtimestamp'."\n";
						
						$code  = '$this->filter->set('.$this->_getClassName().'::'.$field['constant'].'.\'_before\', $before);'."\n";
						$code .= '$this->filter->set('.$this->_getClassName().'::'.$field['constant'].'.\'_after\', $after);';
					} else {
						$param .= '$'.$field['property'];
						
						if(isset($field['values'])){
							$param .= '='.$this->_getClassName().'::'.$this->_makeEnumConstantName($field['field'], $field['default']);
						} else {
							$docblock .= "\t".' * Wildcard: *'."\n";
							$docblock .= "\t".' *'."\n";
						}	
						$docblock .= "\t".' * @param string $'.$field['property']."\n";
						
						$code = '$this->filter->set('.$this->_getClassName().'::'.$field['constant'].', $'.$field['property'].');';
					}
					
					$docblock .= "\t".' */'."\n";
					$methods[$method] = $this->_makeMethod($method, $code, $docblock, $param);
				}
				
			}
			$method_code = '';
			foreach ($methods as $method){
				$method_code .= rtrim($method)."\n";
			}
			$this->_writeCodeBlock($content, 'Filter methods', $method_code);			
		}		
	}		
	protected function _writeListElement(&$content){
		$classvar = $this->_getClassVar();
		if(!preg_match('/s$/', $classvar)){
			$classvar .= 's';
		}
		$content = str_replace('${listelement}', $classvar, $content);
	}

}
?>