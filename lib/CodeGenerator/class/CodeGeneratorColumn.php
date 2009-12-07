<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator table column object.
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
 * CodeGenerator table column.
 *
 * The code generator represents a table  column in a relations database.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorColumn {


	//*****************************************************************//
	//********* CodeGenrator Table Column Class Properties ************//
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
	 * @var integer column type
	 * @internal
	 */
	private $type = CodeGeneratorColumn::TYPE_STRING;

	/**
	 * @var integer column smart type
	 * @internal
	 */
	private $smarttype = null;

	/**
	 * @var boolean read only
	 * @internal
	 */
	private $readonly = false;

	/**
	 * @var boolean sortable
	 * @internal
	 */
	private $sortable = false;

	/**
	 * @var integer field max length
	 * @internal
	 */
	private $maxlength = null;

	/**
	 * @var boolean is index
	 * @internal
	 */
	private $key = false;

	/**
	 * @var boolean is unique
	 * @internal
	 */
	private $unique = false;

	/**
	 * @var mixed default value
	 */
	private $default = null;

	/**
	 * @var boolean has default value
	 */
	private $default_defined = false;

	/**
	 * @var array enum values
	 */
	private $values = false;


	//*****************************************************************//
	//********** CodeGenrator Table Column Class constants ************//
	//*****************************************************************//
	/**
	 * @var integer column type class
	 */
	const TYPE_CLASS = 1;

	/**
	 * @var integer column type string
	 */
	const TYPE_STRING = 3;

	/**
	 * @var integer column type integer
	 */
	const TYPE_INTEGER = 4;

	/**
	 * @var integer column type float
	 */
	const TYPE_FLOAT = 5;

	/**
	 * @var integer column type boolean
	 */
	const TYPE_BOOLEAN = 6;


	/**
	 * @var integer column smart type timestamp
	 */
	const SMARTTYPE_TIMESTAMP = 1001;
	/**
	 * @var integer column smart type blob
	 */
	const SMARTTYPE_BLOB = 1002;
	/**
	 * @var integer column smart type text
	 */
	const SMARTTYPE_TEXT = 1003;
	/**
	 * @var integer column smart type seconds
	 */
	const SMARTTYPE_SECONDS = 1004;


	/**
	 * @var integer column key type primary
	 */
	const KEY_PRIMARY = 2001;

	/**
	 * @var integer column key type index
	 */
	const KEY_INDEX = 2002;

	/**
	 * @var integer column key type unique
	 */
	const KEY_UNIQUE= 2003;


	//*****************************************************************//
	//*********** CodeGenrator Table Column Class methods *************//
	//*****************************************************************//
	/**
	 * Create new table column instance.
	 *
	 * @uses CodeGeneratorColumn::$table
	 * @uses CodeGeneratorColumn::$name
	 * @throws BaseException
	 * @param CodeGeneratorTable $table
	 * @param string $name table column name
	 * @return void
	 */
	public function __construct(CodeGeneratorTable $table, $name){
		if(assert('is_string($name)')){
			$this->table = $table;
			$this->name = $name;
		} else {
			throw new BaseException('$name is not a string', E_USER_ERROR);
		}
	}

	/**
	 * Set column data type.
	 *
	 * Set the column data type and if no smart type is defined, set that as well.
	 *
	 * @uses CodeGeneratorColumn::$type
	 * @uses CodeGeneratorColumn::$smarttype
	 * @param integer $type data type
	 * @return boolean true if succesfull, else return false
	 */
	public function setType($type = CodeGeneratorColumn::TYPE_STRING){
		if(assert('is_integer($type)')){
			$this->type = $type;
			if(is_null($this->smarttype)){
				$this->smarttype = $type;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set column smart type.
	 *
	 * @uses CodeGeneratorColumn::$smarttype
	 * @param integer $smarttype smart data type
	 * @return boolean true if succesfull, else return false
	 */
	public function setSmartType($smarttype){
		if(assert('is_integer($smarttype)')){
			$this->smarttype = $smarttype;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set read-only.
	 *
	 * @uses CodeGeneratorColumn::$readonly
	 * @param boolean $state read-only state
	 * @return boolean true if succesfull, else return false
	 */
	public function setReadOnly($state=false){
		if(assert('is_bool($state)')){
			$this->readonly = $state;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set sortable state.
	 *
	 * @uses CodeGeneratorColumn::$sortable
	 * @param boolean $state sortable
	 * @return boolean true if succesfull, else return false
	 */
	public function setSortable($state=false){
		if(assert('is_bool($state)')){
			$this->sortable = $state;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set max length.
	 *
	 * @uses CodeGeneratorColumn::$maxlength
	 * @param integer $maxlength
	 * @return boolean true if succesfull, else return false
	 */
	public function setMaxLength($maxlength){
		if(assert('is_integer($maxlength)')){
			$this->maxlength = $maxlength;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set default value.
	 *
	 * @param string $default Default value
	 * @return boolean true if succesfull, else return false
	 */
	public function setDefault($default){
		$this->default = $default;
		$this->default_defined = true;
		return true;
	}

	/**
	 * Set index type.
	 *
	 * @uses CodeGeneratorColumn::$key
	 * @uses CodeGeneratorColumn::$unique
	 * @param integer $type index type
	 * @return boolean true if succesfull, else return false
	 */
	public function setKey($type=false){
		if(assert('is_integer($type)')){
			$this->key = $type;
			if($type == CodeGeneratorColumn::KEY_PRIMARY || $type == CodeGeneratorColumn::KEY_UNIQUE){
				$this->unique = true;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set enum values.
	 *
	 * @param array $values enum values
	 * @uses CodeGeneratorColumn::$values
	 * @return boolean true if succesfull, else return false
	 */
	public function setValues(array $values){
		$this->values = $values;
		return true;
	}
}
