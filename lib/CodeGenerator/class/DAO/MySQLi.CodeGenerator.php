<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator objects.
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
 * @internal
 */


//*****************************************************************//
//*************** MySQLi_CodeGenerator DAO class ******************//
//*****************************************************************//
/**
 * MySQLi DAO Class for the CodeGenerator.
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 *
 * @internal
 */
class MySQLi_CodeGenerator extends DatabaseDAO implements Singleton,DAO_CodeGenerator {
	/**
	 * DAO Class instance.
	 *
	 * @var MySQLi_CodeGenerator
	 * @internal
	 */
	private static $instance = null;

	/**
	 * @return MySQLi_CodeGenerator
	 * @internal
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_CodeGenerator();
		}
		return self::$instance;
	}

	/**
	 * Analyse table.
	 *
	 * @see DAO_CodeGenerator::analyseTable()
	 * @return CodeGeneratorTable
	 */
	public function analyseTable($table){
		$cls_table = new CodeGeneratorTable($table);

		$columns = $this->masterQuery(new MySQLiQuery('SHOW FULL COLUMNS FROM `'.$table.'`'));

		$create = $this->masterQuery(new MySQLiQuery('SHOW CREATE TABLE `'.$table.'`'));
		$create = $create->fetchArray();

		if(isset($create['Table'])){
			$create = $create['Create Table'];
		} else {
			trigger_error('This is not a table, use view instead.');
		}


		$table_name = preg_replace('/^tbl_/', '', $table);

		preg_match_all('/^\s*(UNIQUE\s*KEY|PRIMARY\s*KEY|KEY)(\s*`(.*?)`)?\s*\((.*?)\)/m', $create, $match);


		$indexed_columns = array();

		foreach($match[3] as $key => $index){
			switch($match[1][$key]){
				case 'PRIMARY KEY':
					$index = $cls_table->addIndex('PRIMARY');
					$index->setType(CodeGeneratorColumn::KEY_PRIMARY);
					break;
				case 'UNIQUE KEY':
					$index = $cls_table->addIndex($index);
					$index->setType(CodeGeneratorColumn::KEY_UNIQUE);
					break;
				default:
					$index = $cls_table->addIndex($index);
					$index->setType(CodeGeneratorColumn::KEY_INDEX);
					break;
			}

			$index_columns = explode(',', str_replace('`','', $match[4][$key]));
			foreach($index_columns as $column){
				if(!isset($indexed_columns[$column])){
					$indexed_columns[$column] = array();
				}
				$indexed_columns[$column][] = $index;
			}
		}

		while($out = $columns->fetchArray()){

			$cls_field = $cls_table->addColumn($out['Field']);

			if(preg_match('/\(([0-9]+)\)/', $out['Type'], $match)){
				$cls_field->setMaxLength((int) $match[1]);
			}

			$match = array();
			if(preg_match('/^(tiny|small|medium|big)?int/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_INTEGER);
			} else if(preg_match('/^(var)?char/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_STRING);
			} else if(preg_match('/^(tiny)|text/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_STRING);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_TEXT);
			} else if(preg_match('/^(tiny|medium)?blob/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_STRING);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_BLOB);
			} else if(preg_match('/^(var)?binary/', $out['Type'])){
				$cls_field->setType(CodeGeneratorField::TYPE_STRING);
			} else if(preg_match('/^enum\(\'TRUE\',\'FALSE\'\)/i', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_BOOLEAN);
			} else if(preg_match('/^(enum|set)\((.*?)\)/i', $out['Type'], $match)){
				$cls_field->setType(CodeGeneratorColumn::TYPE_STRING);

				$match = explode(',', $match[2]);
				$values = array();
				foreach ($match as $value){
					$values[] = preg_replace('/^[\'"](.*?)[\'"]$/', '\\1', $value);
				}
				$cls_field->setValues($values);
			} else if(preg_match('/^(date|timestamp|datetime|year)/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_INTEGER);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_TIMESTAMP);
			} else if(preg_match('/^(date|timestamp|datetime|time|year)/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_INTEGER);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_TIMESTAMP);
			} else if(preg_match('/^(time)/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_INTEGER);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_SECONDS);
			} else if(preg_match('/^(float|double|real|decimal)/', $out['Type'])){
				$cls_field->setType(CodeGeneratorColumn::TYPE_FLOAT);
			} else {
				trigger_error('Unknown datatype: '.$out['Type'], E_USER_ERROR);
			}


			if(!empty($out['Default'])){
				switch($out['Default']){
					case 'TRUE':
						$cls_field->setDefault(true);
						break;
					case 'FALSE':
						$cls_field->setDefault(false);
						break;
					default:
						$cls_field->setDefault($out['Default']);
				}
			} else if($out['Null'] == 'YES'){
				$cls_field->setDefault(null);
			}

			if(!empty($out['Key'])){
				switch ($out['Key']){
					case 'PRI':
						if(!$cls_field->isForeignKey()){
							$cls_field->setReadOnly(true);
						}
						$cls_field->setKey(CodeGeneratorColumn::KEY_PRIMARY);
						break;
					case 'UNI':
						$cls_field->setKey(CodeGeneratorColumn::KEY_UNIQUE);
						$cls_field->setSortable(true);
						break;
					case 'MUL':
						$cls_field->setKey(CodeGeneratorColumn::KEY_INDEX);
						$cls_field->setSortable(true);
						break;
				}
			}
			if(isset($indexed_columns[$out['Field']])){
				foreach ($indexed_columns[$out['Field']] as $index){
					$index->addColumn($cls_field);
					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY && !$cls_field->isForeignKey()){
						$cls_field->setReadOnly(true);
					}
					$cls_field->setKey($index->getType());
				}
			}

			if(preg_match('/^enum/', $out['Type']) && !preg_match('/TRUE.*?FALSE/', $out['Type'])){
				preg_match_all('/[\'"](.*?)[\'"]/', $out['Type'], $matches);
				$field['values'] = $matches[1];
			}

			if(preg_match('/^(timestamp|datetime)/', $out['Type'])){
				if(preg_match('/`'.$out['Field'].'`.*?ON UPDATE CURRENT_TIMESTAMP/', $create)){
					$cls_field->setReadOnly(true);
				}
				if($out['Field'] == 'create_timestamp'){
					$cls_field->setReadOnly(true);
					$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);
				}
			}

			if($out['Field'] == 'sort_order'){
				$cls_field->setReadOnly(true);
				$cls_field->setSmartType(CodeGeneratorColumn::SMARTTYPE_SORT_ORDER);
			}

			if($reference = $this->_lookupReference($cls_table, $cls_field)){
				$cls_field->setReference($reference);
			}
		}
		return $cls_table;
	}


	/**
	 * Analyse view.
	 *
	 * @todo complete integration
	 * @see DAO_CodeGenerator::analyseView()
	 * @return CodeGeneratorView
	 * @internal
	 */
	public function analyseView($view){
		$dbtables = $this->masterQuery(new MySQLiQuery('SHOW TABLES'));


		$create = $this->masterQuery(new MySQLiQuery('SHOW CREATE VIEW `'.$view.'`'));
		$create = $create->fetchArray();
		list(,$create['Create View']) = explode('AS', $create['Create View'], 2);
		preg_match_all('/`(.*?)`/i', $create['Create View'], $matches);
		$entities = array_unique($matches[1]);

		$viewcolumns = $this->masterQuery(new MySQLiQuery('DESC `'.$view.'`'));
		while($out = $viewcolumns->fetchArray()){
			$columns[] = $out['Field'];
		}

		while($out = $dbtables->fetchArray()){
			if(in_array($out[0], $entities)){
				$tables[] = $this->analyseTable($out[0]);
			}
		}
		$view = new CodeGeneratorView($view);

		foreach($tables as $table){
			while(list(,$column) = $table->eachColumn()){
				if(in_array($column->getName(), $columns)){
					$view->addViewColumn($column);

				}
			}

		}
		return $view;
	}

	/**
	 * Lookup class name from mysql reference.
	 *
	 * @param CodeGeneratorTable $table
	 * @param CodeGeneratorColumn $field
	 * @return string class name if class could be found, else return false
	 * @internal
	 */
	private function _lookupReference(CodeGeneratorTable $table, CodeGeneratorColumn $field){
		$create = $this->masterQuery(new MySQLiQuery('SHOW CREATE TABLE `'.$table->getName().'`'));
		$create = $create->fetchArray();
		if(isset($create['Table'])){
			$create = $create['Create Table'];
		} else {
			$create = $create['Create View'];
		}
		if(preg_match('/CONSTRAINT.*?FOREIGN\s+KEY\s*\(`'.preg_quote($field->getName(), '/').'`\).*?REFERENCES.*?`(.*?)`.*?\(`(.*?)`\)/', $create, $match)){
			$reference_table = new CodeGeneratorTable($match[1]);
			$column = $reference_table->addColumn($match[2]);
			if(CodeGeneretorNameResolver::getInstance()->isColumnPrimaryKey($column)){
				return $column->getName();
			} else if($table->getName() == $reference_table->getName()){
				return $table->getPrimaryKey()->getName();
			} else {
				return $this->_lookupReference($reference_table, $column);
			}
		}
		return false;
	}
}
?>