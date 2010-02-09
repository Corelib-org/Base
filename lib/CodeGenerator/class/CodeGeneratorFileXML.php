<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator xml file object
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
//***************** CodeGenrator XML File class *******************//
//*****************************************************************//
/**
 * CodeGenerator xml file.
 *
 * The code generator represents a xml file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorFileXML extends CodeGeneratorFile {


	//*****************************************************************//
	//************ CodeGenrator XML File class methods ****************//
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
	protected function _getCodeBlock(&$source, $block, $prefix='<!--', $suffix='-->'){
		$block = new CodeGeneratorCodeBlockXML($source, $block, $prefix, $suffix);
		if($block->exist()){
			return $block;
		} else {
			return false;
		}
	}
}
?>