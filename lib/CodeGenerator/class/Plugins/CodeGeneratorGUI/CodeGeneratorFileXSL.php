<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator XSL file object
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
//***************** CodeGenrator XSL File class *******************//
//*****************************************************************//
/**
 * CodeGenerator XSL file.
 *
 * The code generator represents a XSL file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorFileXSL extends CodeGeneratorFileXML {


	//*****************************************************************//
	//************** CodeGeneratorFileXSL properties ******************//
	//*****************************************************************//
	/**
	 * @var string base prefix
	 */
	protected $base_prefix = '';


	//*****************************************************************//
	//**************** CodeGeneratorFileXSL methods *******************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @uses CodeGeneratorFileXSL::_setBasePrefix()
	 * @param CodeGeneratorTable $table
	 * @param DOMElement $settings
	 * @param string $prefix
	 * @param string $group
	 * @return void
	 */
	public function __construct(CodeGeneratorTable $table, DOMElement $settings=null, $prefix=null, $group=null){
		parent::__construct($table, $settings, $prefix, $group);
		if(!is_null($group)){
			$this->_setBasePrefix(str_repeat('../', 3));
		} else {
			$this->_setBasePrefix(str_repeat('../', 2));
		}
	}

	/**
	 * Set base prefix
	 *
	 * The base prefix is the prefix needed to create
	 * a relative path to the xsl base directory. this is
	 * usually handled by {@link CodeGeneratorFileXSL::__construct()},
	 * but it can be changed if needed.
	 *
	 * @uses CodeGeneratorFileXSL::$base_prefix
	 * @return boolean true on success, else return false
	 */
	protected function _setBasePrefix($prefix){
		assert('is_string($prefix)');
		$this->base_prefix = $prefix;
		return true;
	}

	/**
	 * Write XSL Layout mode.
	 *
	 * replaces all occurences of ${xsl-layout-mode} with $mode.
	 *
	 * @param string $content
	 * @param string $mode
	 * @return boolean true on success, else return false
	 */
	protected function _writeXSLLayoutMode(&$content, $mode){
		$content = str_replace('${xsl-layout-mode}', $mode, $content);
		return true;
	}

	/**
	 * Write layout template filename.
	 *
	 * replaces all occurences of ${layout} with $template
	 *
	 * @uses CodeGeneratorFileXSL::$base_prefix
	 * @param string $content
	 * @param string $mode
	 * @return boolean true on success, else return false
	 */
	protected function _writeXSLLayoutTemplate(&$content, $template){
		$content = str_replace('${layout}', $this->base_prefix.$template, $content);
		return true;
	}

	/**
	 * Write template filename.
	 *
	 * replaces all occurences of ${templates} with $template
	 *
	 * @uses CodeGeneratorFileXSL::$base_prefix
	 * @param string $content
	 * @param string $mode
	 * @return boolean true on success, else return false
	 */
	protected function _writeXSLTemplate(&$content, $template){
		$content = str_replace('${templates}', $this->base_prefix.$template, $content);
		return true;
	}
}
?>