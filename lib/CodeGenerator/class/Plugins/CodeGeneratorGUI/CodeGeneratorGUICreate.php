<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator gui plugin definition class.
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
//******************** CodeGeneratorGUICreate class *********************//
//*****************************************************************//
/**
 * CodeGenerator gui create view
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorGUICreate extends CodeGeneratorGUIFileXSL {


	//*****************************************************************//
	//*************** CodeGeneratorGUICreate methods ******************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorGUICreate.
	 *
	 * @uses CodeGeneratorTable::getTableReadableVariableName()
	 * @uses CodeGeneratorFile::getTable()
	 * @uses CodeGeneratorFile::_setFilename()
	 * @uses CodeGeneratorFile::_loadContent()
	 * @uses CodeGeneratorFile::$settings
	 * @uses CodeGeneratorFileXML::_getReadableGroup()
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

		$prefix .= 'share/xsl/pages/';

		if(!is_null($group)){
			$prefix .= $this->_getReadableGroup($group).'/';
		}

		if($this->settings->getAttribute('action') == 'default'){
			$filename = 'create';
		} else {
			$filename = 'create-'.$this->settings->getAttribute('action');
		}

		$this->_setFilename($prefix.$this->getTable()->getTableReadableVariableName().'/'.$filename.'.xsl');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Create.xsl.generator');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeXSLLayoutMode($this->content, $this->settings->getAttribute('xsl-layout-mode'));
		$this->_writeXSLLayoutTemplate($this->content, $this->settings->getAttribute('layout'));
		$this->_writeXSLTemplate($this->content, 'base/'.$this->_getReadableGroup($this->getGroup()).'/'.$this->getTable()->getTableReadableVariableName().'.xsl');

		$fields = array();
		$list = $this->settings->getElementsByTagName('field');
		for($i = 0; $i < $list->length; $i++){
			$fields[] = $list->item($i)->getAttribute('name');
		}

		if(sizeof($fields) == 0){
			$fields = false;
		}

		if($block = $this->_getCodeBlock($this->content, 'Create')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if(($fields === false || in_array($column->getName(), $fields)) && (!$this->hasField($block, $column)) && $column->isWritable()){
					$block->addComponent($this->writeField($block, $column));
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}
	}

	/**
	 * Check to see if a create field allready exists.
	 *
	 * This method may be overwritten to support a
	 * new implementation of {@link CodeGeneratorGUICreate::writeField()}
	 *
	 * @see CodeGeneratorGUICreate::writeField()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true if view template allready has field
	 */
	public function hasField(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		return $block->hasStatementRegex('/xsl\:call\-template.*?name\=\".*?'.$column->getFieldReadableVariableName().'\".*?\n/ms');
	}

	/**
	 * Write create field.
	 *
	 * write field to and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUICreate::hasField()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeField(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$call = new CodeGeneratorCodeBlockXMLElement('xsl:call-template');
		$call->setAttribute('name', $this->getFieldEditID($column));

		$param = $call->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:with-param'));
		$param->setAttribute('name', $column->getFieldReadableVariableName());
		$param->setAttribute('select', '/page/settings/get/'.$column->getFieldReadableVariableName());
		return $call;
	}
}
?>