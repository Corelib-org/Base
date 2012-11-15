<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator model plugin dao file class.
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Sørensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @since Version 5.0
 */


//*****************************************************************//
//************ CodeGeneratorModelFileDAOMySQLi class **************//
//*****************************************************************//
/**
 * CodeGenerator model dao file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 * @todo Add uses docblock tags to methods
 */
class CodeGeneratorModelFileDAOMySQLi extends CodeGeneratorFilePHP {


	//*****************************************************************//
	//********** CodeGeneratorModelFileDAOMySQLi methods **************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorModelFileDAOMySQLi.
	 *
	 * @param CodeGeneratorTable $table
	 * @param DOMElement $settings
	 * @param string $prefix
	 * @param string $group
	 * @see CodeGeneratorFile::__construct()
	 * @return void
	 * @internal
	 */
	public function __construct(CodeGeneratorTable $table, DOMElement $settings=null, $prefix=null, $group=null){
		parent::__construct($table, $settings, $prefix, $group);

		$prefix .= 'lib/class/';

		if(!is_null($group)){
			$prefix .= $group.'/';
		}

		$this->_setFilename($prefix.'Lib/DAO/MySQLi.'.$this->getTable()->getClassName().'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelDAOMySQLi.php.generator');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeSelectColumns($this->content);
		$this->_writeSpecialCreateFields($this->content);
		$this->_writeSpecialUpdateFields($this->content);
		$this->_writeDeleteActions($this->content);
		$this->_writeUtilityMethods($this->content);
		$this->_writeNMRelationChanges($this->content);
	}

	/**
	 * Create database field name.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return return string field name
	 */
	protected function _createFieldName(CodeGeneratorColumn $column){
		return '`\'.'.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'`';
	}

	/**
	 * Write select columns.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function _writeSelectColumns(&$content){
		if($block = $this->_getCodeBlock($content, 'getSelectColumns')){
			$count = $this->getTable()->countColumns();
			$i = 0;
			while(list(,$column) = $this->getTable()->eachColumn()){
				if(!$block->hasStatementRegex('/'.$this->getTable()->getClassName().'\:\:'.$column->getFieldConstantName().'\./')){
					if($column->getSmartType() == CodeGeneratorColumn::SMARTTYPE_TIMESTAMP || $column->getSmartType() == CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE){
						$col = 'UNIX_TIMESTAMP('.$this->_createFieldName($column).') AS '.$this->_createFieldName($column);
					} else if($column->getType() == CodeGeneratorColumn::TYPE_BOOLEAN){
						$col ='IF('.$this->_createFieldName($column).'=\\\'TRUE\\\', true, false) AS '.$this->_createFieldName($column);
					} else {
						$col = $this->_createFieldName($column);
					}
					if($block->getLineCount() == 2){
						if($i > 0){
							$prefix = ', ';
						} else {
							$prefix = '  ';
						}
					} else if($i == 0){
						if($block->getLineCount() > 2){
							$prefix = ', ';
						} else {
							$prefix = '  ';
						}
					} else {
						$prefix = ', ';
					}
					$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$columns .= \''.$prefix.$col.'\';'));
					$i++;
				}
			}
			$this->_writeCodeBlock($content, $block);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write relationship changes.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeNMRelationChanges(&$content){
		$joins = array();
		$primary = $this->getTable()->getPrimaryKey();
		if($primary && $primary->countColumns() > 1){
			while(list(,$column) = $primary->eachColumn()){
				$dao[] = '$'.$column->getFieldVariableName();
				$where[] = '`\'.'.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'` = \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'';
				$replace[] = $column->getTable()->getClassName().'::'.$column->getFieldConstantName();
				// $where_update[] = '`\'.'.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'`=?';
			}
			$content = preg_replace('/\s*\/\*\*\n\s*\*.*?\n\s*\*\/(\n\s*public\s*function\s+create)/m','\\1', $content);
			$content = preg_replace('/public\s*function\s+create\s*\(.*?(\/\*\*|function)/ms','\\1', $content);

			$content = preg_replace('/(function\s+read\s*\()(.*?)(\))/','\\1'.implode(', ', $dao).'\\3', $content);
			$content = preg_replace('/(function\s+delete\s*\()(.*?)(\))/','\\1'.implode(', ', $dao).'\\3', $content);
			$content = preg_replace('/(function\s+update\s*\()(.*?)(,\s*DatabaseDataHandler)/','\\1'.implode(', ', $dao).'\\3', $content);


			if($this->getTable()->countColumns() == $primary->countColumns()){
				$update = '\\1$query = MySQLiTools::makeReplaceStatement(\''.$this->getTable()->getName().'\', array('.implode(', ', $replace).'));';
			} else {
				$update  = '\\1/* Special create fields */\\1/* Special create fields end */\\1';
				$update .= '\\1$columns_create = $data->getUpdatedColumns();';
				$update .= '\\1$values_create = $data->getUpdatedColumnValues();\\1';
				$update .= '\\1$query = MySQLiTools::makeInsertStatement(\''.$this->getTable()->getName().'\', $columns_create, \'ON DUPLICATE KEY UPDATE \'.MySQLiTools::makeUpdateColumns($columns));';
			}

			$content = preg_replace('/^(\s*)\$query\s*=\s*MySQLiTools::makeUpdateStatement.*?$/ms', $update, $this->content);

			$content = preg_replace('/(^\s*WHERE\s*).*?FIELD_ID.*?$(?<!;)/m','\\1'.implode(' AND ', $where).'', $content);
			$content = preg_replace('/(^\s*WHERE\s*).*?FIELD_ID.*?$(?<=;)/m','\\1'.implode(' AND ', $where).'\';', $content);

			if($this->getTable()->countColumns() == $primary->countColumns()){
				$content = preg_replace('/(MySQLiQueryStatement\s*\(.*?)(\$values\s*,\s*)(\$id)/','\\1'.implode(', ', $dao).'', $content);
			} else {
				$content = preg_replace('/(MySQLiQueryStatement\s*\(.*?)(\$values\s*,\s*)(\$id)/','\\1$values_create, '.implode(', ', $dao).', $values', $content);
			}
		}
		return true;
	}

	/**
	 * Write special create fields.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeSpecialCreateFields(&$content){
		if($block = $this->_getCodeBlock($content, 'Special create fields')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				switch ($column->getSmartType()){
					case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE:
						if(!$block->hasStatement('$data->setSpecialValue ( '.$column->getTable()->getClassName().'::'.$column->getFieldConstantName())){
							$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->setSpecialValue('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().', \'NOW()\');'));
						}
						break;
					case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP:
						if($column->isWritable()){
							if(!$block->hasStatement('$data->isChanged ( '.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().' ) ')){
								$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$data->isChanged('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')'));
							}
							if(!$block->hasStatement('$data->setSpecialValue ( '.$column->getTable()->getClassName().'::'.$column->getFieldConstantName())){
								$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->setSpecialValue('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().', \'FROM_UNIXTIME(?)\');'));
							}
						}
						break;
				}
				if($column->getName() == 'sort_order' && !$block->hasStatement('$this->_getMaxOrder()')){
					$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->set('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().', $this->_getMaxOrder() + 1);'));
				}
			}

			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE){
					$changes = array();
					$available = array();
					if(!$block->hasStatement(' ! $this->is'.$index->getIndexMethodName().'Available (')){
						while(list(,$column) = $index->eachColumn()){
							$changes[] = '$data->isChanged('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')';
							$available[] = '$data->get('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')';
						}
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('!$this->is'.$index->getIndexMethodName().'Available('.str_repeat('null, ', $this->getTable()->getPrimaryKey()->countColumns()).implode(', ', $available).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
					}
				}
			}
			$primary = $this->getTable()->getPrimaryKey();
			if($primary && $primary->countColumns() > 1){
				while(list(,$column) = $primary->eachColumn()){
					if(!$block->hasStatement('$data->removeExcludeField ( '.$primary->getTable()->getClassName().'::'.$column->getFieldConstantName())){
						$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->removeExcludeField('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().');'));
					}
				}
			}

			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write special update fields.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeSpecialUpdateFields(&$content){
		if($block = $this->_getCodeBlock($content, 'Special update fields')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				switch ($column->getSmartType()){
					case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP:
						if($column->isWritable()){
							if(!$block->hasStatement('$data->isChanged ( '.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().' ) ')){
								$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$data->isChanged('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')'));
								$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->setSpecialValue('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().', \'FROM_UNIXTIME(?)\');'));
							}
						}
						break;
				}
				/*
				if($column->getName() == 'sort_order' && !$block->hasStatement('$this->_getMaxOrder()')){
					$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->set('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().', $this->_getMaxOrder() + 1);'));
				}
				*/
			}
			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE){
					$changes = array();
					$available = array();
					if(!$block->hasStatement(' ! $this->is'.$index->getIndexMethodName().'Available (')){
						while(list(,$column) = $index->eachColumn()){
							$changes[] = '$data->isChanged('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')';
							$available[] = '$data->get('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().')';
						}
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('('.implode(' || ', $changes).') && !$this->is'.$index->getIndexMethodName().'Available($id, '.implode(', ', $available).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
					}
				}
			}
			$primary = $this->getTable()->getPrimaryKey();
			if($primary && $primary->countColumns() > 1){
				while(list(,$column) = $primary->eachColumn()){
					if(!$block->hasStatement('$data->addExcludeField ( '.$primary->getTable()->getClassName().'::'.$column->getFieldConstantName())){
						$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$data->addExcludeField('.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().');'));
					}
				}
			}

			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write delete actions.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeDeleteActions(&$content){
		if($block = $this->_getCodeBlock($content, 'Delete actions')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->getName() == 'sort_order'){
					if(!$block->hasStatement('$this->_cleanSortOrder')){
						$parameters = array();
						while(list(,$key) = $column->getTable()->getPrimaryKey()->eachColumn()){
							$parameters[] = '$'.$key->getFieldVariableName();
						}
						$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_cleanSortOrder('.implode(', ', $parameters).');'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write utility methods.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeUtilityMethods(&$content){
		if($block = $this->_getCodeBlock($content, 'Utility methods')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->getName() == 'sort_order'){
					$primary = $column->getTable()->getPrimaryKey();
					$parameters = array();
					while(list(,$key) = $primary->eachColumn()){
						$docblocks[] = new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, true).' $'.$column->getFieldVariableName());
//						$select[] = '`\'.'.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'`';
						$where[] = $this->_createFieldName($key).' = \\\'\'.$this->escapeString($'.$key->getFieldVariableName().').\'\\\'';
//						$nwhere[] = '`\'.'.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'` != \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\''; */
						$parameters[] = '$'.$key->getFieldVariableName();
					}


					$method = 'moveUp';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
						foreach($parameters as $parameter){
							$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($parameter));
						}

						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Move object up in the manual sort order'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('see', $this->getTable()->getClassName().'_DAO::moveUp()'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->_getSortOrder('.implode(', ', $parameters).');'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$order = $query['.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'];'));
						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$order > 1'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'UPDATE `'.$column->getTable()->getName().'`'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          SET '.$this->_createFieldName($column).' ='.$this->_createFieldName($column).'+1'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.$this->_createFieldName($column).' < \\\'\'.$this->escapeString($order).\'\\\''));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          ORDER BY '.$this->_createFieldName($column).' DESC'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          LIMIT 1\';'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->query(new MySQLiQuery($query));'));

						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'UPDATE `'.$column->getTable()->getName().'`'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          SET '.$this->_createFieldName($column).'='.$this->_createFieldName($column).'-1'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.implode(' AND ', $where).'\';'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->query(new MySQLiQuery($query));'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));

						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
					}

					$method = 'moveDown';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
						foreach($parameters as $parameter){
							$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($parameter));
						}

						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Move object down in the manual sort order'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('see', $this->getTable()->getClassName().'_DAO::moveDown()'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->_getSortOrder('.implode(', ', $parameters).');'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$order = $query['.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'];'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'SELECT '.$this->_createFieldName($column)));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          FROM  `'.$column->getTable()->getName().'`'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          ORDER BY '.$this->_createFieldName($column).'` DESC'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          LIMIT 1\';'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->query(new MySQLiQuery($query));'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $query->fetchArray();'));

						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$order != $query['.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().']'));

						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'UPDATE `'.$column->getTable()->getName().'`'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          SET '.$this->_createFieldName($column).' ='.$this->_createFieldName($column).'-1'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.$this->_createFieldName($column).' > \\\'\'.$this->escapeString($order).\'\\\''));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          ORDER BY '.$this->_createFieldName($column).' ASC'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          LIMIT 1\';'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->query(new MySQLiQuery($query));'));

						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'UPDATE `'.$column->getTable()->getName().'`'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          SET '.$this->_createFieldName($column).'='.$this->_createFieldName($column).'+1'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.implode(' AND ', $where).'\';'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->query(new MySQLiQuery($query));'));

						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));

						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
					}

					$method = '_getSortOrder';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('private', $method));
						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Get object sort order'));
						foreach($docblocks as $doc){
							$docblock->addComponent($doc);
						}
						foreach($parameters as $parameter){
							$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($parameter));
						}
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'integer object sort_order'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'SELECT '.$this->_createFieldName($column)));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          FROM  `'.$column->getTable()->getName().'`'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.implode(' AND ', $where)));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          LIMIT 1\';'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->query(new MySQLiQuery($query));'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $query->fetchArray();'));
					}

					$method = '_getMaxOrder';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('private', $method));
						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Get maximum sort order'));
						foreach($docblocks as $doc){
							$docblock->addComponent($doc);
						}
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'integer maximum sort_order'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'SELECT '.$this->_createFieldName($column)));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          FROM  `'.$column->getTable()->getName().'`'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          ORDER BY '.$this->_createFieldName($column).' DESC'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          LIMIT 1\';'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->query(new MySQLiQuery($query));'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $query->fetchArray();'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $query[\'sort_order\'];'));
					}

					$method = '_cleanSortOrder';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('private', $method));
						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Cleanup sort order before deleting a object'));
						foreach($docblocks as $doc){
							$docblock->addComponent($doc);
						}
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));

						foreach($parameters as $parameter){
							$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($parameter));
						}

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->_getSortOrder('.implode(', ', $parameters).');'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$order = $query['.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().'];'));


						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'UPDATE `'.$column->getTable()->getName().'`'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          SET '.$this->_createFieldName($column).'='.$this->_createFieldName($column).'-1'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.$this->_createFieldName($column).' > \\\'\'.$this->escapeString($order).\'\\\'\';'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->query(new MySQLiQuery($query));'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
					}

				}
			}

			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE || $index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
					$changes = array();
					$available = array();

					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
						$method = 'getByID';
						$indexname = 'ID';
					} else {
						$method = 'getBy'.$index->getIndexMethodName();
						$indexname = $index->getName();
					}
					if(!$block->hasMethod($method)){

						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
						$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Get row by '.$indexname));

						$where = array();
						while(list(,$column) = $index->eachColumn()){
							$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, false).' $'.$column->getFieldVariableName()));
							$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
							$where[] = $this->_createFieldName($column).' = \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'';
						}
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'mixed array similar of read method, else return false'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'SELECT \'.$this->getSelectColumns().\''));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          FROM `'.$this->getTable()->getName().'`'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.implode(' AND ', $where).'\';'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->slaveQuery(new MySQLiQuery($query));'));

						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$query->getNumRows() > 0'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $query->fetchArray();'));
						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
					}

					if($index->getType() != CodeGeneratorColumn::KEY_PRIMARY){
					$method = 'is'.$index->getIndexMethodName().'Available';
						if(!$block->hasMethod($method)){
							$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
							$docblock = $method->setDocBlock(new CodeGeneratorCodeBlockPHPDoc('Check if '.$index->getName().' is available.'));
							$where = array();
							$nwhere = array();
							$select = array();
							$parameters = array();

							while(list(,$column) = $this->getTable()->getPrimaryKey()->eachColumn()){
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, false).' $'.$column->getFieldVariableName()));
								$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
								$parameters[] = '`'.$column->getName().'` != \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'';
							}

							while(list(,$column) = $index->eachColumn()){
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, true).' $'.$column->getFieldVariableName()));
								$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
								$select[] = $this->_createFieldName($column);
								$where[] = $this->_createFieldName($column).' = \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'';
								$nwhere[] = $this->_createFieldName($column).' != \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'';

							}
							$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));

							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = \'SELECT '.implode(', ', $select)));
							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          FROM `'.$this->getTable()->getName().'`'));
							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('          WHERE '.implode(' AND ', $where).'\';'));

							$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('!is_null($id)'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query .= \' AND '.implode(' AND ', $parameters).'\';'));

							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$query = $this->slaveQuery(new MySQLiQuery($query));'));

							$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$query->getNumRows() > 0'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
							$else = $if->addAlternate();
							$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
						}
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}
}
?>