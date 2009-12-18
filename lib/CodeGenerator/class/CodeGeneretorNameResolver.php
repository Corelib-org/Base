<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator name resolver.
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
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('CODE_GENERATOR_NAME_RESOLVER_ENGINE')){
	/**
	 * Code generator resolver engine to use.
	 *
	 * @var string resolver engine class name
	 * @since Version 5.0
	 */
	define('CODE_GENERATOR_NAME_RESOLVER_ENGINE', 'CodeGeneretorNameResolverEngineDefault');
}


//*****************************************************************//
//********** CodeGeneretorNameResolverEngine interfaces ***********//
//*****************************************************************//
/**
 * Name resolver engine.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
interface CodeGeneretorNameResolverEngine {
	/**
	 * Get class name from table.
	 *
	 * @param CodeGeneratorTable $table
	 * @return string class name
	 */
	public function getClassName(CodeGeneratorTable $table);

	/**
	 * Get reference class from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string class name
	 */
	public function getReferenceClassName(CodeGeneratorColumn $column);

	/**
	 * Get field constant name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field constant name
	 */
	public function getFieldConstantName(CodeGeneratorColumn $column);

	/**
	 * Get field var name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field var name
	 */
	public function getFieldVariableName(CodeGeneratorColumn $column);

	/**
	 * Get field method name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field method name
	 */
	public function getFieldMethodName(CodeGeneratorColumn $column);

	/**
	 * Detect whether column is a primary key.
	 *
	 * @return boolean true if primary key, else return false
	 */
	public function isColumnPrimaryKey(CodeGeneratorColumn $column);

	/**
	 * Detect whether column is a foreign key.
	 *
	 * @return boolean true if foreign key, else return false
	 */
	public function isColumnForeignKey(CodeGeneratorColumn $column);

	/**
	 * Get index method name from index.
	 *
	 * @param CodeGeneratorIndex $index
	 * @return string index method name
	 */
	public function getIndexMethodName(CodeGeneratorIndex $column);
}


//*****************************************************************//
//***************** CodeGeneretorNameResolver class ***************//
//*****************************************************************//
/**
 * Code generator name resolver.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneretorNameResolver implements CodeGeneretorNameResolverEngine,Singleton {


	//*****************************************************************//
	//********* CodeGeneretorNameResolver Class Properties ************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var CodeGeneretorNameResolver
	 * @internal
	 */
	private static $instance = null;

	/**
	 * @var CodeGeneretorNameResolverEngine
	 * @internal
	 */
	private $engine = null;


	//*****************************************************************//
	//*********** CodeGeneretorNameResolver Class methods *************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	private function __construct(){
		eval('$this->engine = new '.CODE_GENERATOR_NAME_RESOLVER_ENGINE.'();');
	}

	/**
	 * 	Return instance of CodeGeneretorNameResolver.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses CodeGeneretorNameResolver::$instance
	 *	@return Base
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new CodeGeneretorNameResolver();
		}
		return self::$instance;
	}

	/**
	 * Get class name from table.
	 *
	 * Get class name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getClassName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorTable $table
	 * @return string Class name
	 */
	public function getClassName(CodeGeneratorTable $table){
		return $this->engine->getClassName($table);
	}

	/**
	 * Get reference class from column.
	 *
	 * Get reference class name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getReferenceClassName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string class name
	 */
	public function getReferenceClassName(CodeGeneratorColumn $column){
		return $this->engine->getReferenceClassName($column);
	}

	/**
	 * Get field constant name from column.
	 *
	 * Get field constant name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getFieldConstantName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string Class name
	 */
	public function getFieldConstantName(CodeGeneratorColumn $column){
		return $this->engine->getFieldConstantName($column);
	}

	/**
	 * Get field var name from column.
	 *
	 * Get field var name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getFieldVariableName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string Class name
	 */
	public function getFieldVariableName(CodeGeneratorColumn $column){
		return $this->engine->getFieldVariableName($column);
	}

	/**
	 * Get field method name from column.
	 *
	 * Get field method name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getFieldMethodName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string Class name
	 */
	public function getFieldMethodName(CodeGeneratorColumn $column){
		$method = $this->engine->getFieldMethodName($column);
		if(strtolower($method) == 'id'){
			$method = 'ID';
		}
		return $method;
	}

	/**
	 * Detect whether column is a primary key.
	 *
	 * Detect if column is a primary key from  CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::isColumnPrimaryKey()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string Class name
	 */
	public function isColumnPrimaryKey(CodeGeneratorColumn $column){
		return $this->engine->isColumnPrimaryKey($column);
	}

	/**
	 * DDetect whether column is a foreign key.
	 *
	 * Detect if column is a foreign key from  CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::isColumnForeignKey()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorColumn $column
	 * @return string Class name
	 */
	public function isColumnForeignKey(CodeGeneratorColumn $column){
		return $this->engine->isColumnForeignKey($column);
	}

	/**
	 * Get index method name from index.
	 *
	 * Get index method name from CodeGeneratorNameResolverEngine.
	 *
	 * @uses CodeGeneretorNameResolver::$engine
	 * @uses CodeGeneretorNameResolverEngine::getIndexMethodName()
	 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
	 * @param CodeGeneratorIndex $column
	 * @return string Class name
	 */
	public function getIndexMethodName(CodeGeneratorIndex $column){
		$method = $this->engine->getIndexMethodName($column);
		return $method;
	}
}


//*****************************************************************//
//******** CodeGeneretorNameResolverEngineDefault Class ***********//
//*****************************************************************//
/**
 * Default code generator name resolver engine.
 *
 * This engine is used by {@link CodeGeneretorNameResolver} in order to
 * do name lookups based on the default corelib database naming scheme.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 * @see CODE_GENERATOR_NAME_RESOLVER_ENGINE
 */
class CodeGeneretorNameResolverEngineDefault implements CodeGeneretorNameResolverEngine {


	//*****************************************************************//
	//***** CodeGeneretorNameResolverEngineDefault Class methods ******//
	//*****************************************************************//
	/**
	 * Get class name from table.
	 *
	 * @param CodeGeneratorTable $table
	 * @return string Class name
	 */
	public function getClassName(CodeGeneratorTable $table){
		$name = preg_replace('/^tbl_/', '', $table->getName());
		$name = str_replace('_', ' ', $name);
		$name = ucwords($name);
		$name = str_replace(' ', '', $name);
		$name = $this->_convertPluralToSingular($name);
		return $name;
	}

	/**
	 * Get reference class name from table.
	 *
	 * @param CodeGeneratorTable $column
	 * @return string Class name
	 */
	public function getReferenceClassName(CodeGeneratorColumn $column){
		if($name = $column->getReference()){
			$name = preg_replace('/^pk_/', '', $name);
			$name = str_replace('_', ' ', $name);
			$name = ucwords($name);
			$name = str_replace(' ', '', $name);
			$name = $this->_convertPluralToSingular($name);
			return $name;
		} else {
			return false;
		}
	}

	/**
	 * Get field constant name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field constant name
	 */
	public function getFieldConstantName(CodeGeneratorColumn $column){
		$var = $this->getFieldVariableName($column);
		$var = 'FIELD_'.strtoupper($var);
		return $var;
	}

	/**
	 * Get field var name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field var name
	 */
	public function getFieldVariableName(CodeGeneratorColumn $column){
		if($this->isColumnForeignKey($column)){
			$var = preg_replace('/^fk_/', '', $column->getName());
			$var = $this->_convertPluralToSingular($var);
		} else if($this->isColumnPrimaryKey($column)){
			$var = 'id';
		}  else {
			$var = $column->getName();
		}

		if($column->getTable()->getClassVariable() == $var){
			throw new BaseException('Field and class name variable collision class: '.$column->getTable()->getName().' ('.$column->getTable()->getClassName().') and colum: '.$column->getName(), E_USER_ERROR);
		}
		return $var;
	}

	/**
	 * Get field method name from column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field method name
	 */
	public function getFieldMethodName(CodeGeneratorColumn $column){
		$var = $this->getFieldVariableName($column);
		$var = str_replace('_', ' ', $var);
		$var = ucwords($var);
		$var = str_replace(' ', '', $var);
		return $var;
	}

	/**
	 * Detect whether column is a primary key.
	 *
	 * @return boolean true if primary key, else return false
	 */
	public function isColumnPrimaryKey(CodeGeneratorColumn $column){
		return preg_match('/^pk_/', $column->getName());
	}

	/**
	 * Detect whether column is a foreign key.
	 *
	 * @return boolean true if foreign key, else return false
	 */
	public function isColumnForeignKey(CodeGeneratorColumn $column){
		return preg_match('/^fk_/', $column->getName());
	}

	/**
	 * Get index method name from index.
	 *
	 * @param CodeGeneratorIndex $index
	 * @return string field method name
	 */
	public function getIndexMethodName(CodeGeneratorIndex $index){
		$var = $index->getName();
		$var = str_replace('_', ' ', $var);
		$var = ucwords($var);
		$var = str_replace(' ', '', $var);
		return $var;
	}

	/**
	 * Convert plural word to singular.
	 *
	 * @param string $string
	 * @return string
	 * @internal
	 */
	private function _convertPluralToSingular($string){
		if(preg_match('/(ses|oes)$/', $string)){
			$string = preg_replace('/es$/', '', $string);
		} else if(preg_match('/(ies)$/', $string)){
			$string = preg_replace('/ies$/', 'y', $string);
		} else if(preg_match('/s$/', $string)){
			$string = preg_replace('/s$/', '', $string);
		}
		return $string;
	}

}
?>