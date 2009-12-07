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
	 */
	private $columns = array();


	//*****************************************************************//
	//*************** CodeGenrator Table Class Methods ****************//
	//*****************************************************************//
	/**
	 * Create new table instance.
	 *
	 * @uses CodeGeneratorTable::$name
	 * @param string $table Table name
	 * @return void
	 */
	public function __construct($name){
		$this->name = $name;
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
	 * Iterate over table columns.
	 *
	 * Do iteration over table columns and return a object representing
	 * each column.
	 *
	 * @uses CodeGeneratorTable::$columns
	 * @return CodeGeneratorColumn table column
	 */
	public function each(){
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
}
