<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator php file object
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
//***************** CodeGenrator PHP File class *******************//
//*****************************************************************//
/**
 * CodeGenerator php file.
 *
 * The code generator represents a php file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorFilePHP extends CodeGeneratorFile {


	//*****************************************************************//
	//************ CodeGenrator PHP File class methods ****************//
	//*****************************************************************//
	/**
	 * Get codeblock from source.
	 *
	 * @uses CodeGeneratorCodeBlock::exist()
	 * @param string $source Source code string
	 * @param string $block Block name
	 * @param string $prefix Block prefix
	 * @param string $suffix Block suffix
	 * @return CodeGeneratorCodeBlockPHP if block exist, else return false
	 */
	protected function _getCodeBlock(&$source, $block, $prefix='/*', $suffix='*/'){
		$block = new CodeGeneratorCodeBlockPHP($source, $block, $prefix, $suffix);
		if($block->exist()){
			return $block;
		} else {
			return false;
		}
	}

	/**
	 * Get PHP valid data type for column.
	 *
	 * @param CodeGeneratorColumn $column
	 * @param boolean $mixed Allw mixed datatype, this is handy when writing data types in phpdoc
	 * @return string data type
	 */
	protected function _getColumnDataType(CodeGeneratorColumn $column, $mixed=false){
		$type = false;
		switch ($column->getType()){
			case CodeGeneratorColumn::TYPE_BOOLEAN:
				$type = 'bool';
				break;
			case CodeGeneratorColumn::TYPE_FLOAT:
				$type = 'float';
				break;
			case CodeGeneratorColumn::TYPE_INTEGER:
				$type = 'integer';
				break;
			case CodeGeneratorColumn::TYPE_STRING:
				$type = 'string';
				break;
			default:
				if($mixed){
					$type = 'mixed';
				}
		}
		return $type;
	}
}
?>