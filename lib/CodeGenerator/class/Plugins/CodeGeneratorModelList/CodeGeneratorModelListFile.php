<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator model list plugin file class.
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
//*************** CodeGeneratorModelListFile class ****************//
//*****************************************************************//
/**
 * CodeGenerator model dao file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 * @todo Add uses docblock tags to methods
 */
class CodeGeneratorModelListFile extends CodeGeneratorModelFile {


	//*****************************************************************//
	//***************** CodeGeneratorModel methods ********************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorModelListFile.
	 *
	 * @param CodeGeneratorTable $table
	 * @param DOMElement $settings
	 * @param string $prefix
	 * @param string $group
	 * @see CodeGeneratorFile::__construct()
	 * @return void
	 * @internal
	 */
	public function __construct($table, $settings, $prefix, $group){
		CodeGeneratorFilePHP::__construct($table, $settings, $prefix, $group);

		if(!is_null($prefix)){
			$prefix .= '/';
		} else {
			$prefix .= 'lib/class/';
		}
		if(!is_null($group)){
			$prefix .= $group.'/';
		}

		$this->_setFilename($prefix.'Lib/'.$this->getTable()->getClassName().'List.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelList.php');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeConverters($this->content);
		$this->_writeOrders($this->content);
		$this->_writeFilters($this->content);
		$this->_writeListElement($this->content);
		$this->_writeRelationChanges($this->content);
	}

	/**
	 * Write column converters.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	protected function _writeConverters(&$content){
		parent::_writeConverters($content);
		if($block = $this->_getCodeBlock($content, 'Set converters')){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $this->getTable()->eachColumn()){
				$method = 'set'.$column->getFieldMethodName().'Converter';
				$property = $column->getFieldVariableName().'_converter';
				$converters = $xpath->query('generator[@name = \'CodeGeneratorModel\']/field[@name = "'.$column->getName().'" and @converter="true"]', $this->getSettings()->parentNode)->length;
				$smarttypes = array(CodeGeneratorColumn::SMARTTYPE_SECONDS,
				                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP,
				                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);
				if($converters > 0 || in_array($column->getSmartType(), $smarttypes)){
					if(!$block->hasStatement('! is_null ( $this->'.$this->_makeConverterVariableName($column).' ) ')){
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('!is_null($this->'.$this->_makeConverterVariableName($column).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $item->set'.$column->getFieldMethodName().'Converter($this->'.$this->_makeConverterVariableName($column).');'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write column order methods.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeOrders(&$content){
		if($block = $this->_getCodeBlock($content, 'Order methods')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				$method = 'set'.$column->getFieldMethodName().'OrderDesc';
				if(!$block->hasMethod($method) && $column->isSortable()){
					$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
					$docblock = new CodeGeneratorCodeBlockPHPDoc('Set '.$column->getFieldVariableName().' sort order descending');
					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
					$method->setDocBlock($docblock);
					$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->order->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().', DATABASE_ORDER_DESC);'));
				}
				$method = 'set'.$column->getFieldMethodName().'OrderAsc';
				if(!$block->hasMethod($method) && $column->isSortable()){
					$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
					$docblock = new CodeGeneratorCodeBlockPHPDoc('Set '.$column->getFieldVariableName().' sort order ascending');
					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
					$method->setDocBlock($docblock);
					$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->order->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().', DATABASE_ORDER_ASC);'));
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Write filters.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeFilters(&$content){
		if($block = $this->_getCodeBlock($content, 'Filter methods')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				$method = 'set'.$column->getFieldMethodName().'Filter';
				if(!$block->hasMethod($method) && $column->isSortable()){
					$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
					$docblock = new CodeGeneratorCodeBlockPHPDoc('Set filter for '.$column->getFieldVariableName());
					$method->setDocBlock($docblock);

					$smarttypes = array(CodeGeneratorColumn::SMARTTYPE_SECONDS,
					                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP,
					                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);

					if(in_array($column->getSmartType(), $smarttypes)){
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', 'integer $from Unixtimestamp'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', 'integer $to Unixtimestamp'));

						$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$from', 'null'));
						$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$to', 'null'));

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->filter->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'_from\', $from);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->filter->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'_to\', $to);'));
					} else {
						$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
						if($class = $this->_getReferenceClass($column)){
							$param->setType($class);
							$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' '.'$'.$column->getFieldVariableName()));
							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->filter->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().', $'.$column->getFieldVariableName().'->getID());'));
						} else {
							$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, true).' '.'$'.$column->getFieldVariableName()));
							$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->filter->set('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().', $'.$column->getFieldVariableName().');'));
						}
					}
					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success else return false'));
				}
			}

			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write list element name.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeListElement(&$content){
		$content = str_replace('${listelement}', $this->getTable()->getTableReadableVariableName().'-list', $content);
	}

	/**
	 * Write relationship changes.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeRelationChanges(&$content){
		$primary = $this->getTable()->getPrimaryKey();
		if($primary && $primary->countColumns() > 1){
			while(list(,$column) = $primary->eachColumn()){
				$keys[] = '$out['.$column->getTable()->getClassName().'::'.$column->getFieldConstantName().']';
 			}
 			$content = str_replace('$out['.$this->getTable()->getClassName().'::FIELD_ID]', implode(', ', $keys), $content);
		}
	}
}
?>