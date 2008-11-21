<?php
class CodeGeneratorModelFileDAOMySQLi extends CodeGeneratorModelFileBase {
	public function __construct($path, $classname, $table, $fields){
		$path .= 'Lib/DAO/';
		parent::__construct($path, $classname, $table, $fields);
		$this->_setFilename('MySQLi.'.$classname.'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelDAOMySQLi.php');
	}	
	
	public function generate(){
		$this->_writeSelectColumns($this->content);
		$this->_rewriteReadQuery($this->content);
		$this->_writeAfterChangeActions($this->content);
		$this->_writeSpecialCreateFields($this->content);
		$this->_writeSpecialUpdateFields($this->content);
		$this->_writeDeleteActions($this->content);
		$this->_writeUtilityMethods($this->content);
		$this->_writeTableName($this->content);
		$this->_writeClassName($this->content);
		
		if($this->_isNMRelationTable()){
			$this->_writeNMRelatoionChanges($this->content);
		}		
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
		
		if(strstr($content, '${selectcolumns}')){
			$content = str_replace('${selectcolumns}', implode(",\n\t                        ", $select_colums), $content);
		} else {
			if(preg_match('/const\s*SELECT_COLUMNS\s*=\s*[\'|"](.*?)[\'|"];\s*\n/s', $content, $match)){
				$new_columns = array();
				foreach ($select_colums as $column){
					if(!strstr($match[1], $column)){
						$new_columns[] = $column;
					}
				}
				if(sizeof($new_columns) > 0){
					array_unshift($new_columns, $match[1]);
					$content = str_replace($match[1], implode(",\n\t                        ", $new_columns), $content);	
				}
			}
		}
	}

	private function _writeSpecialCreateFields(&$content){
		$conditions = array();
		if($block = $this->_getCodeBlock($content, 'Special create fields')){
			$lines = preg_split("/(\n|\r)/", $block);
			$in_condition = false;
			foreach ($lines as $key => $line){
				if(preg_match('/\s+if\(.*?'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = $condition[1];
				} else if($in_condition !== false){
					$conditions[$in_condition] .= $line."\n";
					if(preg_match('/^\t\t\}/', $line)){
						$in_condition = false;
					}
				} else if(preg_match('/'.$this->_getClassName().'::([A-Z0-9_]+)\b/', $line, $condition)){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).rtrim($line)."\n";
					$in_condition = false;
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
					if($field['field'] == 'sort_order' && !isset($conditions[$field['constant']])){
						$conditions[$field['constant']] = "\t\t".'$data->set('.$this->_getClassName().'::'.$field['constant'].', $this->_getMaxOrder() + 1);';	
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
			
			
			foreach ($this->content_tables as $table => $fields){
				foreach ($fields as $field){
					if($field['constant'] != strtoupper('FIELD_'.$this->_getClassName().'_ID')){
						if(!isset($conditions[$field['constant']])){
							$conditions[$field['constant']] = "\t\t".'$data->addExcludeField('.$this->_getClassName().'::'.$field['constant'].');';
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
					$in_condition = false;
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
			
			foreach ($this->content_tables as $table => $fields){
				foreach ($fields as $field){
					if($field['constant'] != strtoupper('FIELD_'.$this->_getClassName().'_ID')){
						if(!isset($conditions[$field['constant']])){
							$conditions[$field['constant']] = "\t\t".'$data->addExcludeField('.$this->_getClassName().'::'.$field['constant'].');';
						}
					}
				}
			}				
			
			if($this->_isNMRelationTable()){
				$fields = $this->_getPrimaryFields();
				foreach ($fields as $field){
					if(!isset($conditions[$field['constant']])){
						$conditions[$field['constant']] = "\t\t".'$data->set('.$this->_getClassName().'::'.$field['constant'].', $'.$field['property'].');';
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

	private function _writeAfterChangeActions(&$content){
		$this->_writeActions($content, 'After edit actions');	
		$this->_writeActions($content, 'After create actions', "\t");	
	}
	
	private function _writeDeleteActions(&$content){
		if($block = $this->_getCodeBlock($content, 'Delete actions')){
			$conditions = array();
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
				} elseif (preg_match('/\>(.*?)\(/', $line, $condition) && $in_condition === false){
					$conditions[$condition[1]] = $this->_getCommentBlock($lines, $key).$line;
				}
			}

			foreach ($this->fields as $field){
				if($field['field'] == 'sort_order' && !isset($conditions['_cleanSortOrder'])){
					$conditions['_cleanSortOrder'] = "\t\t".'$this->_cleanSortOrder($id);';
				}
			}
			
			$condition_code = '';
			foreach ($conditions as $condition){
				$condition_code .= rtrim($condition)."\n";
			}			
			$this->_writeCodeBlock($content, 'Delete actions', $condition_code);
		}		
	}
	
	private function _writeActions(&$content, $blockname, $indent=''){
		if($block = $this->_getCodeBlock($content, $blockname)){
			$conditions = array();
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
			
			foreach ($this->content_tables as $table => $fields){
				foreach ($fields as $field){
					if($field['constant'] != strtoupper('FIELD_'.$this->_getClassName().'_ID')){
						if(!isset($conditions[$field['constant']])){
							$method = $this->_createMethodName('_update', $field['property']);
							$conditions[$field['constant']] = "\t\t".$indent.'$this->'.$method.'($id, $data->get('.$this->_getClassName().'::'.$field['constant'].'));';
						}
					}
				}
			}	

			$condition_code = '';
			foreach ($conditions as $condition){
				$condition_code .= rtrim($condition)."\n";
			}			
			$this->_writeCodeBlock($content, $blockname, $condition_code);
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
					if(preg_match('/^\t\}/', $line) && isset($lines[($key + 1)]) && preg_match('/\t\/\*\*/', $lines[($key + 1)])){
						$in_method = false;
					}
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
				if($field['field'] == 'sort_order'){
					$method = 'moveUp';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * @see ${classname}_DAO::moveUp'."\n";
					$docblock .= "\t".' */'."\n";
					
					$code  = '$query = $this->_getSortOrder($id);'."\n";
					$code .= '$order = $query['.$this->_getClassName().'::'.$field['constant'].']; '."\n\n";
					$code .= 'if($order > 1){'."\n";
					$code .= '	$query = \'UPDATE `${tablename}`'."\n";
					$code .= '	          SET `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=`\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`+1'."\n";
					$code .= '	          WHERE `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` <  \\\'\'.mysql_escape_string($order).\'\\\''."\n";
					$code .= '	          ORDER BY `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` DESC'."\n";
					$code .= '	          LIMIT 1\';'."\n";
					$code .= '	$this->query(new MySQLiQuery($query));'."\n";
					$code .= '	$query = \'UPDATE `${tablename}`'."\n";
					$code .= '	          SET `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=\'.'.$this->_getClassName().'::'.$field['constant'].'.\'-1'."\n";
					$code .= '	          WHERE `\'.'.$this->_getClassName().'::FIELD_ID.\'`=\\\'\'.mysql_escape_string($id).\'\\\'\';'."\n";
					$code .= '	$this->query(new MySQLiQuery($query));'."\n";
					$code .= '	return true;'."\n";
					$code .= '} else {'."\n";
					$code .= '	return false;'."\n";
					$code .= '}';
					
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id');
					}
					

					$method = 'moveDown';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * @see ${classname}_DAO::moveDown'."\n";
					$docblock .= "\t".' */'."\n";
					
					$code  = '$query = $this->_getSortOrder($id);'."\n";
					$code .= '$order = $query['.$this->_getClassName().'::'.$field['constant'].']; '."\n\n";
					
					$code .= '$query = \'SELECT `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`'."\n";
					$code .= '          FROM `${tablename}`'."\n";
					$code .= '          ORDER BY `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` DESC'."\n";
					$code .= '          LIMIT 1\';'."\n";
					$code .= '$query = $this->query(new MySQLiQuery($query));'."\n";
					$code .= '$query = $query->fetchArray();'."\n\n";
					
					$code .= 'if($order != $query['.$this->_getClassName().'::'.$field['constant'].']){'."\n";
					$code .= '	$query = \'UPDATE `${tablename}`'."\n";
					$code .= '	          SET `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=`\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`-1'."\n";
					$code .= '	          WHERE `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` >  \\\'\'.mysql_escape_string($order).\'\\\''."\n";
					$code .= '	          ORDER BY `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` ASC'."\n";
					$code .= '	          LIMIT 1\';'."\n";
					$code .= '	$this->query(new MySQLiQuery($query));'."\n";
					$code .= '	$query = \'UPDATE `${tablename}`'."\n";
					$code .= '	          SET `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=`\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`+1'."\n";
					$code .= '	          WHERE `\'.'.$this->_getClassName().'::FIELD_ID.\'`=\\\'\'.mysql_escape_string($id).\'\\\'\';'."\n";
					$code .= '	$this->query(new MySQLiQuery($query));'."\n";
					$code .= '	return true;'."\n";
					$code .= '} else {'."\n";
					$code .= '	return false;'."\n";
					$code .= '}';
					
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id');
					}

					
					$method = '_getSortOrder';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Get object sort order.'."\n";
					$docblock .= "\t".' * '."\n";
					$docblock .= "\t".' * @param integer $id object id'."\n";
					$docblock .= "\t".' * @return integer object sort_order'."\n";
					$docblock .= "\t".' */'."\n";					
					
					$code  = '$query = \'SELECT `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`'."\n";
					$code .= '          FROM `${tablename}`'."\n";
					$code .= '          WHERE `\'.'.$this->_getClassName().'::FIELD_ID.\'`=\\\'\'.mysql_escape_string($id).\'\\\''."\n";
					$code .= '          LIMIT 1\';'."\n";
					$code .= '$query = $this->query(new MySQLiQuery($query));'."\n";
					$code .= 'return $query->fetchArray();'."\n";
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id', 'private');
					}
					

					$method = '_getMaxOrder';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Get maximum sort order.'."\n";
					$docblock .= "\t".' * '."\n";
					$docblock .= "\t".' * @return integer maximum sort_order'."\n";
					$docblock .= "\t".' */'."\n";					
					
					$code  = '$query = \'SELECT `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`'."\n";
					$code .= '          FROM `${tablename}`'."\n";
					$code .= '          ORDER BY `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` DESC'."\n";
					$code .= '          LIMIT 1\';'."\n";
					$code .= '$query = $this->query(new MySQLiQuery($query));'."\n";
					$code .= '$query = $query->fetchArray();'."\n";
					$code .= 'return $query['.$this->_getClassName().'::'.$field['constant'].'];'."\n";
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '', 'private');
					}
					
					$method = '_cleanSortOrder';
					
					$docblock  = '/**'."\n";
					$docblock .= "\t".' * Cleanup sort order before deleting a object.'."\n";
					$docblock .= "\t".' * '."\n";
					$docblock .= "\t".' * @param integer $id object to be deleted'."\n";
					$docblock .= "\t".' */'."\n";					
					
					$code  = '$query = $this->_getSortOrder($id);'."\n";
					$code .= '$order = $query['.$this->_getClassName().'::'.$field['constant'].'];'."\n";
					$code .= '$query = \'UPDATE `${tablename}`'."\n";
					$code .= '          SET `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=`\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`-1'."\n";
					$code .= '          WHERE `\'.'.$this->_getClassName().'::'.$field['constant'].'.\'` > \\\'\'.mysql_escape_string($order).\'\\\'\';'."\n";
					$code .= '$this->query(new MySQLiQuery($query));'."\n";
					
					if(!isset($methods[$method])){
						$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id', 'private');
					}
				}
			}
			
			
			foreach ($this->content_tables as $table => $fields){
				foreach ($fields as $field){
					if($field['constant'] != strtoupper('FIELD_'.$this->_getClassName().'_ID')){
						if(!isset($conditions[$field['constant']])){
							$method = $this->_createMethodName('_update', $field['property']);
							
							$docblock  = '/**'."\n";
							$docblock .= "\t".' * Update '.$field['property'].'.'."\n";
							$docblock .= "\t".' * '."\n";
							$docblock .= "\t".' * @param integer $id object id'."\n";
							$docblock .= "\t".' * @param string $content content'."\n";
							$docblock .= "\t".' * @return boolean true on success, else return false'."\n";
							$docblock .= "\t".' */'."\n";		
							
							$code  = '$query = MySQLiTools::makeReplaceStatement(\''.$field['table'].'\','."\n";
							$code .= '                                           array(\''.$this->_convertTableToForeignKey($this->_getTableName()).'\', '.$this->_getClassName().'::'.$field['constant'].'));'."\n";
							$code .= '$statement = new MySQLiQueryStatement($query, $id, $content);'."\n";
							$code .= '$query = $this->masterQuery($statement);'."\n";
							$code .= 'return true;';
							
							if(!isset($methods[$method])){
								$methods[$method] = $this->_makeMethod($method, $code, $docblock, '$id, $content', 'private');
							}
						}
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
	
	
	private function _rewriteReadQuery(&$content){
		if(preg_match('/function\sread\((.*?(\n\s*WHERE)).*?\t}\n/s', $content, $match)){
			$joins = array();
			
			preg_match('/SELECT(.*?)FROM/s', $match[0], $select);
			$columns = preg_split('/,\s*/s', $select[1]);
			foreach ($columns as $key => $val){
				$columns[$key] = trim($val);	
			}
			
			foreach ($this->content_tables as $table => $fields){
				foreach ($fields as $field){
					if($field['constant'] != strtoupper('FIELD_'.$this->_getClassName().'_ID')){
						if(!in_array('`'.$field['field'].'`', $columns)){
							$columns[] = '`'.$field['field'].'`';	
						}
						if(!preg_match('/LEFT\s*JOIN\s*(`)*'.$field['table'].'/s', $match[0])){
							$joins[] = 'LEFT JOIN `'.$field['table'].'` ON `'.$this->_getTableName().'`.`'.$this->_convertTableToPrimaryKey($this->_getTableName()).'`=`'.$field['table'].'`.`'.$this->_convertTableToForeignKey($this->_getTableName()).'`';
						}
					}
				}
			}
			if(sizeof($joins) > 0){
				$join = str_replace($match[2], "\n\t\t          ".implode("\n\t\t          ", $joins).$match[2], $match[0]);
				$join = str_replace($select[1], ' '.implode(", \n\t\t                 ", $columns)."\n\t\t          ", $join);
				$content = str_replace($match[0], $join, $content);
			}
		}
	}
	
	private function _convertTableToForeignKey($table){
		return preg_replace('/(tbl_)/', 'fk_', $table);	
	}
	private function _convertTableToPrimaryKey($table){
		return preg_replace('/(tbl_)/', 'pk_', $table);	
	}


	private function _writeNMRelatoionChanges(&$content){
		$fields = $this->_getPrimaryFields();
		
		$param_read = array();
		$param_construct = array();
		$param_construct_cmd = array();
		foreach ($fields as $field){ 
			$param_dao_update[] = '$'.$field['property'];
			$param_read[] = '`\'.'.$this->_getClassName().'::'.$field['constant'].'.\'`=\\\'\'.mysql_escape_string($'.$field['property'].').\'\\\'';
		}
		$param_dao_update = implode(", ", $param_dao_update);
		$param_read = implode(' AND ', $param_read);
		
		$content = preg_replace('/(makeUpdateStatement)\((\'.*?\', \$columns)(.*)\);/', 'makeReplaceStatement(\\2);', $content);
		$content = str_replace('$query = $this->masterQuery(new MySQLiQueryStatement($query, $values, $id));', '$query = $this->masterQuery(new MySQLiQueryStatement($query, $values));', $content);
		
		$content = preg_replace('/(function (update|delete|read)\()(\$id)/', '\\1'.$param_dao_update, $content);
		
		$lines = explode("\n", $content);
		$in_create = false;
		$in_read = false;
		$in_delete = false;
		foreach ($lines as $num => $line){
			if($in_create){
				$create .= $line."\n";
				if(preg_match('/^\t\}/', $line)){
					$in_create = false;
				}
			}
			if($in_read){
				$read .= $line."\n";
				if(preg_match('/^\t\}/', $line)){
					$in_read = false;
				}
			}
			if($in_delete){
				$delete .= $line."\n";
				if(preg_match('/^\t\}/', $line)){
					$in_delete = false;
				}
			}
			if(strstr($line, 'function create(')){
				$create  = $this->_getCommentBlock($lines, $num);
				$create .= $line."\n";
				$in_create = true;
			}
			if(strstr($line, 'function read(')){
				$read  = $this->_getCommentBlock($lines, $num);
				$read .= $line."\n";
				$in_read = true;
			}
			if(strstr($line, 'function delete(')){
				$delete  = $this->_getCommentBlock($lines, $num);
				$delete .= $line."\n";
				$in_delete = true;
			}
		}
		if(isset($create)){
			$content = str_replace($create, '', $content);
		}
		if(isset($read)){
			if(strstr($read, $this->_getClassName().'::FIELD_ID')){
				$read_new = preg_replace('/(WHERE\s*).*/', '\\1'.$param_read.'\';', $read);
				$content = str_replace($read, $read_new, $content);
			}
		}
		if(isset($delete)){
			if(strstr($delete, $this->_getClassName().'::FIELD_ID')){
				$delete_new = preg_replace('/(WHERE\s*).*/', '\\1'.$param_read.'\';', $delete);
				$content = str_replace($delete, $delete_new, $content);
			}
		}
	}
}
?>