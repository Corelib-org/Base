<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator table object.
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
//*********************** CodeGenrator Table **********************//
//*****************************************************************//
/**
 * CodeGenerator table.
 *
 * The code generator represents a table in a relations database. a instance
 * of this object is returned when the code generator is done analyzing a table.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorTable {


	//*****************************************************************//
	//************** CodeGenrator Table Class Properties **************//
	//*****************************************************************//
	/**
	 * Table name.
	 *
	 * @var string Table name
	 * @internal
	 */
	private $name = null;

	/**
	 * Columns in table.
	 *
	 * @var array columns in table
	 * @internal
	 */
	private $columns = array();

	/**
	 * indexes in table.
	 *
	 * @var array indexes in table
	 * @internal
	 */
	private $indexes = array();

	/**
	 * Name resolver.
	 *
	 * @var CodeGeneretorNameResolver
	 * @internal
	 */
	private $resolver = null;

	/**
	 * Primary key.
	 *
	 * @var CodeGeneratorIndex
	 * @internal
	 */
	private $primary = null;

	//*****************************************************************//
	//*************** CodeGenrator Table Class Methods ****************//
	//*****************************************************************//
	/**
	 * Create new table instance.
	 *
	 * @uses CodeGeneratorTable::$name
	 * @uses CodeGeneratorTable::$resolver
	 * @uses CodeGeneretorNameResolver::getInstance()
	 * @param string $table Table name
	 * @return void
	 */
	public function __construct($name){
		$this->name = $name;
		$this->resolver = CodeGeneretorNameResolver::getInstance();
	}

	/**
	 * Get table name.
	 *
	 * @uses CodeGeneratorTable::$name
	 * @return string table name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Get class variable name.
	 *
	 * @uses CodeGeneratorTable::$resolver
	 * @uses CodeGeneretorNameResolver::getClassVariable()
	 * @return string class variable
	 */
	public function getClassVariable(){
		$variable = $this->getClassName();
		$variable = preg_replace('/([a-z])([A-Z])/', '\\1_\\2', $variable);
		$variable = strtolower($variable);
		return $variable;
	}

	/**
	 * Get class name.
	 *
	 * @uses CodeGeneratorTable::$resolver
	 * @uses CodeGeneretorNameResolver::getClassName()
	 * @return string class name
	 */
	public function getClassName(){
		return $this->resolver->getClassName($this);
	}

	/**
	 * Get column count.
	 *
	 * Count the number of columns in talbe.
	 *
	 * @return integer column count
	 */
	public function countColumns(){
		return count($this->columns);
	}

	/**
	 * Iterate over table columns.
	 *
	 * Do iteration over table columns and return a object representing
	 * each column.
	 *
	 * @uses CodeGeneratorTable::$columns
	 * @return CodeGeneratorColumn table column
	 */
	public function eachColumn(){
		if($column = each($this->columns)){
			return $column;
		} else {
			reset($this->columns);
			return false;
		}
	}

	/**
	 * Add column to column list
	 *
	 * @uses CodeGeneratorTable::$columns
	 * @param string $fieldname table column name
	 * @return CodeGeneratorColumn
	 */
	public function addColumn($column){
		return $this->columns[$column] = new CodeGeneratorColumn($this, $column);
	}

	/**
	 * Add index to index list
	 *
	 * @uses CodeGeneratorTable::$indexes
	 * @param string $indexname index name
	 * @return CodeGeneratorIndex
	 */
	public function addIndex($column){
		return $this->indexes[$column] = new CodeGeneratorIndex($this, $column);
	}

	/**
	 * Set primary key.
	 *
	 * @uses CodeGeneratorTable::$primary
	 * @param CodeGeneratorIndex $index
	 * @return CodeGeneratorIndex
	 */
	public function setPrimaryKey(CodeGeneratorIndex $index){
		return $this->primary = $index;
	}

	/**
	 * Get primary key.
	 *
	 * @uses CodeGeneratorTable::$primary
	 * @return CodeGeneratorIndex
	 */
	public function getPrimaryKey(){
		return $this->primary;
	}

	/**
	 * Get table readable variable name.
	 *
	 * @uses CodeGeneratorColumn::$resolver
	 * @uses CodeGeneretorNameResolver::getFieldVariableName()
	 * @return string field constant name
	 */
	public function getTableReadableVariableName(){
		return str_replace('_', '-', $this->getClassVariable());
	}



	/**
	 * Iterate over table indexes.
	 *
	 * Do iteration over table indexes and return a object representing
	 * each index.
	 *
	 * @uses CodeGeneratorTable::$indexes
	 * @return CodeGeneratorIndex table column
	 */
	public function eachIndex(){
		if($index = each($this->indexes)){
			return $index;
		} else {
			reset($this->indexes);
			return false;
		}
	}
}
