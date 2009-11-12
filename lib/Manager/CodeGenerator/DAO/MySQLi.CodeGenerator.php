<?php 
class MySQLi_CodeGenerator extends DatabaseDAO implements Singleton,DAO_CodeGenerator {
	private static $instance = null;
	
	/**
	 *	@return MySQLi_CodeGenerator
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_CodeGenerator();
		}
		return self::$instance;	
	}	
	
	public function analyseTable($table){
		$columns = $this->masterQuery(new MySQLiQuery('SHOW FULL COLUMNS FROM `'.$table.'`'));
		
		$create = $this->masterQuery(new MySQLiQuery('SHOW CREATE TABLE `'.$table.'`'));
		$create = $create->fetchArray();
		$create = $create['Create Table'];
		
		$fields = array();
		
		$table_name = preg_replace('/^tbl_/', '', $table);
		while($out = $columns->fetchArray()){
			$field = array('table' => $table,
			               'sortable' => false,
			               'unique' => false,
			               'readonly' => false,
			               'default' => null,
			               'maxlength' => null,
			               'converter' => false);
			
			if(preg_match('/\(([0-9]+)\)/', $out['Type'], $match)){
				$field['maxlength'] = (int) $match[1];
			}
			
			$match = array();
			if(preg_match('/^(tiny|small|medium|big)?int/', $out['Type'])){
				$field['datatype'] = 'integer';
			} else if(preg_match('/^(var)?char/', $out['Type'])){
				$field['datatype'] = 'string';
			} else if(preg_match('/^(tiny)|text/', $out['Type'])){
				$field['smarttype'] = 'text';
				$field['datatype'] = 'string';
			} else if(preg_match('/^(tiny|medium)?blob/', $out['Type'])){
				$field['smarttype'] = 'blob';
				$field['datatype'] = 'string';
			} else if(preg_match('/^(var)?binary/', $out['Type'])){
				$field['datatype'] = 'string';
			} else if(preg_match('/^enum\(\'TRUE\',\'FALSE\'\)/i', $out['Type'])){
				$field['datatype'] = 'boolean';
			} else if(preg_match('/^(enum|set)\((.*?)\)/i', $out['Type'], $match)){
				$match = explode(',', $match[2]);
				$field['values'] = array();
				foreach ($match as $value){
					$field['values'][] = preg_replace('/^[\'"](.*?)[\'"]$/', '\\1', $value);
				}
				$field['datatype'] = 'string';
			} else if(preg_match('/^(date|timestamp|datetime|time|year)/', $out['Type'])){
				$field['datatype'] = 'integer';
				$field['smarttype'] = 'timestamp';
			} else if(preg_match('/^(float|double|real|decimal)/', $out['Type'])){
				$field['datatype'] = 'float';		
			} else {
				trigger_error('Unknown datatype: '.$out['Type'], E_USER_ERROR);
			}
			if(!isset($field['smarttype'])){
				$field['smarttype'] = $field['datatype']; 
			}
			
			if($out['Null'] == 'YES'){
				$field['default'] = 'NULL';
			}
			if(!empty($out['Key'])){
				$field['sortable'] = true;
			}
			if($out['Key'] == 'UNI'){
				$field['unique'] = true;
			}
			switch ($out['Key']){
				case 'PRI':
					$field['keytype'] = 'primary';
					break;			
				case 'UNI':
					$field['keytype'] = 'unique';
					break;			
				case 'MUL':
					$field['keytype'] = 'index';
					break;			
			}
			
			if(!empty($out['Default'])){
				switch($out['Default']){
					case 'TRUE':	
						$default = true;
						break;
					case 'FALSE':
						$default = false;
						break;
					default:
						$default = $out['Default'];
				}
				$field['default'] = $default; 
			}
			if(preg_match('/^enum/', $out['Type']) && !preg_match('/TRUE.*?FALSE/', $out['Type'])){
				preg_match_all('/[\'"](.*?)[\'"]/', $out['Type'], $matches);
				$field['values'] = $matches[1]; 
			}
			if(strstr($out['Field'], 'timestamp') || preg_match('/^(date|timestamp|datetime|time|year)/', $out['Type'])){
				if($out['Field'] == 'create_timestamp' || $out['Field'] == 'edit_timestamp' || preg_match('/`'.$out['Field'].'`.*?ON UPDATE CURRENT_TIMESTAMP/', $create)){
					$field['readonly'] = true;
					$field['default'] = 'NULL';
				}
				if($out['Field'] == 'create_timestamp'){
					$field['smarttype'] = 'on_create_current_timestamp';
				}
				$field['converter'] = true;
			}
			if($out['Field'] == 'sort_order'){
				$field['readonly'] = true;
			}
			
			$field['field'] = $out['Field'];

			
			if($field['field'] == $this->_convertTableToKey($table)){
				$const = 'FIELD_ID';
				$property = 'id';
				$field['readonly'] = true;
			} else if(preg_match('/^fk_/', $field['field'])){
				$const = preg_replace('/^fk(_'.$table_name.')*_(.*?)/i', '\\3', $field['field']);

				$const = preg_replace('/ies$/', 'y', $const);
				$const = preg_replace('/s$/', '', $const);
								
				$property = $const;
				$const = 'FIELD_'.$const.'_ID';
				
				$class = preg_replace('/^fk_/', 'pk_', $field['field']);
				if(preg_match('/_(.*?)_parent/', $class, $match)){
					$class = 'pk_'.$match[1];
				}
				
				if(!$field['class'] = CodeGeneratorClassResolver::getInstance()->getClass($class)){
					if(preg_match('/\bfk_(.*?)\b/', $out['Comment'], $match)){
						$field['class'] = CodeGeneratorClassResolver::getInstance()->getClass('pk_'.$match[1]);
					}
				}
			} else {
				$const = 'FIELD_'.$field['field'];
				$property = strtolower($field['field']);
			}
			$field['constant'] = strtoupper($const);
			$field['property'] = $property;
			$fields[$field['field']] = $field;
		}
		return $fields;
	}
	
	private function _convertTableToKey($table){
		return preg_replace('/^tbl_/', 'pk_', $table);
	}	
}
?>