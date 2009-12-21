<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator model plugin list dao file class.
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
//********** CodeGeneratorModelListFileDAOMySQLi class ************//
//*****************************************************************//
/**
 * CodeGenerator model list dao file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 * @todo Add uses docblock tags to methods
 */
class CodeGeneratorModelListFileDAOMySQLi extends CodeGeneratorModelFileDAOMySQLi {


	//*****************************************************************//
	//****** CodeGeneratorModelListFileDAOMySQLi methods **************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorModelListFileDAOMySQLi.
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

		$this->_setFilename($prefix.'Lib/DAO'.$this->getTable()->getClassName().'List.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/ModelListDAOMySQLi.php');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeOrderStatement($this->content);
		$this->_writeFilterStatement($this->content);
		$this->_writeRelationChanges($this->content);
	}

	/**
	 * Write order statement.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	private function _writeOrderStatement(&$content){
		if($block = $this->_getCodeBlock($content, 'Order statement')){
			$columns = array();
			if(preg_match('/prepareOrderStatement\(.*?,\s*(.*?)\)/', $block->getSource(), $match)){
				$match = trim($match[1]);
				$match = preg_split('/,\s*/', $match);
				foreach($match as $column){
					$columns[] = $column;
				}
			}
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->isSortable()){
					$constant = $column->getTable()->getClassName().'::'.$column->getFieldConstantName();
					if(!in_array($constant, $columns)){
						$columns[] = $column->getTable()->getClassName().'::'.$column->getFieldConstantName();
					}
				}
			}
			$statement = $order = 'MySQLiTools::prepareOrderStatement($order, '.implode(', ', $columns).');';

			if(!$block->hasStatement('MySQLiTools::prepareOrderStatement ( $order')){
				$block->addComponent(new CodeGeneratorCodeBlockPHPStatement($statement));
				$this->_writeCodeBlock($content, $block);
			} else {
				$this->content = preg_replace('/MySQLiTools::prepareOrderStatement\(.*?$/m', $statement, $this->content);
			}
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
	 */
	private function _writeFilterStatement(&$content){
		if($block = $this->_getCodeBlock($content, 'Filter statement')){

			$smarttypes = array(CodeGeneratorColumn::SMARTTYPE_SECONDS,
			                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP,
			                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);

			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->isSortable()){
					if(in_array($column->getSmartType(), $smarttypes)){
						if(!$block->hasStatement('if ( $'.$column->getFieldVariableName().'_from')){
							$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$column->getFieldVariableName().'_from = $this->filter->get('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'_from\')'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$filters[\'where\'] .= \'AND '.$this->_createFieldName($column).' <= FROM_UNIXTIME(\\\'\'.$this->escapeString($'.$column->getFieldVariableName().'_from).\'\\\'\');'));
						}
						if(!$block->hasStatement('if ( $'.$column->getFieldVariableName().'_to')){
							$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$column->getFieldVariableName().'_to = $this->filter->get('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().'.\'_to\')'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$filters[\'where\'] .= \'AND '.$this->_createFieldName($column).' >= FROM_UNIXTIME(\\\'\'.$this->escapeString($'.$column->getFieldVariableName().'_to).\'\\\'\');'));
						}
					}
					if(!$block->hasStatement('if ( $'.$column->getFieldVariableName())){
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$column->getFieldVariableName().' = $this->filter->get('.$this->getTable()->getClassName().'::'.$column->getFieldConstantName().')'));
						if($column->countValues() > 0 || $column->getType() == CodeGeneratorColumn::TYPE_INTEGER || $column->getType() == CodeGeneratorColumn::TYPE_FLOAT){
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$filters[\'where\'] .= \'AND '.$this->_createFieldName($column).' = \\\'\'.$this->escapeString($'.$column->getFieldVariableName().').\'\\\'\');'));
						} else {
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$filters[\'where\'] .= \'AND '.$this->_createFieldName($column).' LIKE \\\'\'.$this->escapeString(MySQLiTools::parseWildcards($'.$column->getFieldVariableName().')).\'\\\'\');'));
						}
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
	 * Write relationship changes.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	private function _writeRelationChanges(&$content){
		if($this->getTable()->getPrimaryKey()->countColumns() > 1){
			if(preg_match('/function getListCount(.*?)AS `count`/s', $content, $match)){
				$result = preg_replace('/(SELECT COUNT\()(.*?)(\) AS `count`)/s', '\\1*\\3', $match[0]);
				$content = str_replace($match[0], $result, $content);
			}
		}
	}
}
?>