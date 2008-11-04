<?php
class CodeGeneratorModelListFileDAOMySQLi extends CodeGeneratorFile {
	public function __construct($path, $classname, $table, $fields){
		$path .= 'Lib/DAO/';
		parent::__construct($path, $classname, $table, $fields);
		$this->_setFilename('MySQLi.'.$classname.'List.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelListDAOMySQLi.php');
	}	
	
	public function generate(){
/*		$this->_writeOrderStatement($this->content);
		$this->_writeFilterStatement($this->content);
		$this->_writeTableName($this->content);
		$this->_writeClassName($this->content);
*/
	}
	
	private function _writeOrderStatement(&$content){
		if($block = $this->_getCodeBlock($content, 'Order statement')){
			$order = array();
			if(preg_match('/^.*?prepareOrderStatement\(.*?,(.*?)\)/', $block, $match)){
				
				$match = trim($match[1]);
				$match = preg_split('/,\s*/', $match);
				foreach ($match as $field){
					$constant = str_replace($this->_getClassName().'::', '', $field);
					$order[$constant] = trim($field); 
				}
			}
			
			foreach ($this->fields as $field){
				if(!isset($order[$field['constant']]) && $field['constant'] != 'FIELD_ID' && isset($field['sortable']) && $field['sortable'] === true){
					$order[$field['constant']] = $this->_getClassName().'::'.$field['constant'];
				}
			}
			if(sizeof($order) > 0){
				$order_code = "\t\t".'$order = \' ORDER BY \'.MySQLiTools::prepareOrderStatement($order, '.implode(', ', $order).');'."\n";	
			} else {
				$order_code = '$order = \'\';'."\n";
			}
			$this->_writeCodeBlock($content, 'Order statement', $order_code);
		}
	}

	private function _writeFilterStatement(&$content){
		
		if($block = $this->_getCodeBlock($content, 'Filter statement')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_condition = false;
			$conditions = array();
			foreach ($lines as $key => $line){
				if(preg_match('/if\(.*?'.$this->_getClassName().'::(.*?)\).*?\)\{/', $line, $loop)){
					$conditions[$loop[1]] = rtrim($line)."\n";
					$in_condition = $loop[1];
				} else if($in_condition !== false){
					$conditions[$in_condition] .= $line."\n";
				}
			}
			foreach ($this->fields as $field){
				$condition = $field['constant'];
				if($field['constant'] != 'FIELD_ID' && !isset($conditions[$condition]) && (isset($field['sortable']) && $field['sortable'] === true)){
					if(strstr($field['smarttype'], 'timestamp')){
						$before = $condition.'.\'_before\'';
						if(!isset($conditions[$before])){
							$code = "\t\t\t".'if($'.$field['property'].'_before = $filter->get('.$this->_getClassName().'::'.$field['constant'].'.\'_before\')){'."\n";
							$code .= "\t\t\t\t".'$filters[\'where\'] .= \'AND `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` >= FROM_UNIXTIME(\\\'\'.mysql_escape_string($'.$field['property'].'_before).\'\\\') \';'."\n";
							$code .= "\t\t\t".'}'."\n";
							$conditions[$before] = $code;
						}
						$after = $condition.'.\'_after\'';
						if(!isset($conditions[$after])){
							$code = "\t\t\t".'if($'.$field['property'].'_after = $filter->get('.$this->_getClassName().'::'.$field['constant'].'.\'_after\')){'."\n";
							$code .= "\t\t\t\t".'$filters[\'where\'] .= \'AND `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` >= FROM_UNIXTIME(\\\'\'.mysql_escape_string($'.$field['property'].'_after).\'\\\') \';'."\n";
							$code .= "\t\t\t".'}';
							$conditions[$after] = $code;
						}
					} else {
						$code  = "\t\t\t".'if($'.$field['property'].' = $filter->get('.$this->_getClassName().'::'.$field['constant'].')){'."\n";
						if(isset($field['values'])){
							$code .= "\t\t\t\t".'$filters[\'where\'] .= \'AND `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` LIKE \\\'\'.mysql_escape_string($'.$field['property'].').\'\\\' \';'."\n";
						} else {
							$code .= "\t\t\t\t".'$filters[\'where\'] .= \'AND `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` LIKE \\\'\'.mysql_escape_string(MySQLiTools::parseWildcards($'.$field['property'].')).\'\\\' \';'."\n";
						}
						
						$code .= "\t\t\t".'}';
						$conditions[$condition] = $code;
					}
				}
			}
			
			
			$condition_code = '';
			foreach ($conditions as $condition){
				$condition_code .= rtrim($condition)."\n";
			}			
			$this->_writeCodeBlock($content, 'Filter statement', $condition_code);		
		}		
	}
}
?>