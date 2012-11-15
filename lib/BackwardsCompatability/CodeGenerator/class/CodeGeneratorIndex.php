<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator table index object.
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
//******************* CodeGenrator Table Column *******************//
//*****************************************************************//
/**
 * CodeGenerator table index.
 *
 * The code generator represents a table index in a relations database.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorIndex {


	//*****************************************************************//
	//********** CodeGenrator Table index Class Properties ************//
	//*****************************************************************//
	/**
	 * @var string column name
	 * @internal
	 */
	private $name = null;

	/**
	 * @var CodeGeneratorTable parent table
	 * @internal
	 */
	private $table = null;

	/**
	 * @see CodeGeneratorColumn::KEY_INDEX
	 * @see CodeGeneratorColumn::KEY_PRIMARY
	 * @see CodeGeneratorColumn::KEY_UNIQUE
	 * @var integer index type
	 * @internal
	 */
	private $type = CodeGeneratorColumn::KEY_INDEX;

	/**
	 * Name resolver.
	 *
	 * @var CodeGeneretorNameResolver
	 * @internal
	 */
	private $resolver = null;

	/**
	 * Columns.
	 *
	 * @var array columns in index
	 * @internal
	 */
	private $columns = array();


	//*****************************************************************//
	//*********** CodeGenrator Table index Class methods **************//
	//*****************************************************************//
	/**
	 * Create new table index instance.
	 *
	 * @uses CodeGeneratorIndex::$table
	 * @uses CodeGeneratorIndex::$name
	 * @throws BaseException
	 * @param CodeGeneratorTable $table
	 * @param string $name table index name
	 * @return void
	 */
	public function __construct(CodeGeneratorTable $table, $name){
		if(assert('is_string($name)')){
			$this->table = $table;
			$this->name = $name;
			$this->resolver = CodeGeneretorNameResolver::getInstance();
		} else {
			throw new BaseException('$name is not a string', E_USER_ERROR);
		}
	}

	/**
	 * Set index type.
	 *
	 * @uses CodeGeneratorIndex::$type
	 * @uses CodeGeneratorIndex::$table
	 * @uses CodeGeneratorTable::setPrimaryKey()
	 * @param integer $type type
	 * @return boolean true if succesfull, else return false
	 */
	public function setType($type = CodeGeneratorColumn::KEY_INDEX){
		if(assert('is_integer($type)')){
			$this->type = $type;
			if($type == CodeGeneratorColumn::KEY_PRIMARY){
				$this->table->setPrimaryKey($this);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add column to index.
	 *
	 * @uses CodeGeneratorIndex::$columns
	 * @return CodeGeneratorColumn
	 */
	public function addColumn(CodeGeneratorColumn $column){
		return $this->columns[] = $column;
	}

	/**
	 * Get index name.
	 *
	 * @uses CodeGeneratorIndex::$name
	 * @return string index name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Get table instance.
	 *
	 * @uses CodeGeneratorIndex::$table
	 * @return CodeGeneratorTable
	 */
	public function getTable(){
		return $this->table;
	}


	/**
	 * Get index type.
	 *
	 * @uses CodeGeneratorIndex::$type
	 * @return integer index type
	 */
	public function getType(){
		return $this->type;
	}


	/**
	 * Get index method name.
	 *
	 * @uses CodeGeneratorColumn::$resolver
	 * @uses CodeGeneretorNameResolver::getIndexMethodName()
	 * @return string field method name
	 */
	public function getIndexMethodName(){
		return $this->resolver->getIndexMethodName($this);
	}

	/**
	 * Get column count.
	 *
	 * Count the number of columns in index.
	 *
	 * @uses CodeGeneratorIndex::$columns
	 * @return integer column count
	 */
	public function countColumns(){
		return count($this->columns);
	}

	/**
	 * Iterate over columns in index.
	 *
	 * Do iteration over index columns and return a array
	 *
	 * @uses CodeGeneratorTable::$columns
	 * @return string column enum value
	 */
	public function eachColumn(){
		if(is_array($this->columns)){
			if($column = each($this->columns)){
				return $column;
			} else {
				reset($this->columns);
				return false;
			}
		}
	}
}
?>