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
//***************** CodeGeneratorGUIPost class ********************//
//*****************************************************************//
/**
 * CodeGenerator gui post file
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorGUIPost extends CodeGeneratorGUIFilePHP {


	//*****************************************************************//
	//**************** CodeGeneratorGUIPost methods *******************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorGUIGet.
	 *
	 * @uses CodeGeneratorTable::getTableReadableVariableName()
	 * @uses CodeGeneratorFile::getTable()
	 * @uses CodeGeneratorFile::_setFilename()
	 * @uses CodeGeneratorFile::_loadContent()
	 * @uses CodeGeneratorFile::$settings
	 * @uses CodeGeneratorFile::_getReadableGroup()
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

		$prefix .= 'lib/http/post/';

		if(!is_null($group)){
			$prefix .= $this->_getReadableGroup($group).'/';
		}

		$this->_setFilename($prefix.$this->getTable()->getTableReadableVariableName().'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Post.php.generator');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->writePageClassName($this->content);
		$this->writeAbstractPageClassName($this->content);

		if($block = $this->_getCodeBlock($this->content, 'Interface post methods')){
			$xpath = new DOMXPath($this->settings->ownerDocument);

			$list = $xpath->query('edit|create', $this->settings);
			if($list->length > 0){
				for($i = 0; $i < $list->length; $i++){
					$method = $list->item($i)->getAttribute('method');
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

						switch($list->item($i)->nodeName){
							case 'create':
								$this->writeCreateMethod($method, $list->item($i));
								break;
							case 'edit':
								$this->writeEditMethod($method, $list->item($i));
								break;
						}
					}
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}


		if($block = $this->_getCodeBlock($this->content, 'Interface post validation')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if((!$this->hasValidator($block, $column)) && $column->isWritable()){
					$this->writeValidator($block, $column);
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}

		if($block = $this->_getCodeBlock($this->content, 'Interface actions')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if((!$this->hasSet($block, $column)) && $column->isWritable()){
					$this->writeSet($block, $column);
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}
	}

	/**
	 * Write get abstract class name.
	 *
	 * Replaces all occurences of ${abstractclassname} with class name from etc/abstracts.php
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	public function writeAbstractPageClassName(&$content){
		$abstracts = file_get_contents('etc/abstracts.php');
		if(preg_match_all('/abstract class (.*?PagePost|.*?Page) extends .*?Page.*?/', $abstracts, $match)){
			foreach($match[1] as $class){
				if(preg_match('/.*?PagePost/', $class)){
					$abstractclass = $class;
					break;
				}
			}
			if(!isset($abstractclass)){
				$abstractclass = $match[1][0];
			}
			$content = str_replace('${abstractclassname}', $abstractclass, $content);
		}
		return true;
	}

	/**
	 * Write create method to template.
	 *
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $create
	 * @return void
	 */
	public function writeCreateMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $create){
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$input = InputHandler::getInstance();'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().' = new '.$this->getTable()->getClassName().'();'));

		$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$this->_validate'.$this->getTable()->getClassName().'Input($'.$this->getTable()->getClassVariable().', '.$this->getFields($create).')'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().'->commit();'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->post->setLocation(\''.$this->getListURL($create).'\');'));
		$else = $if->addAlternate();
		$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->post->setLocation(\''.$this->getCreateURL($create).'?error\', $input->serializePost());'));
	}

	/**
	 * Write edit method to template.
	 *
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $edit
	 * @return void
	 */
	public function writeEditMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $edit){
		$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$id'));

		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$input = InputHandler::getInstance();'));
		// $method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().' = new '.$this->getTable()->getClassName().'($id);'));



		$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$this->getTable()->getClassVariable().' = '.$this->getTable()->getClassName().'::getByID($id)'));

		$if = $if->addComponent(new CodeGeneratorCodeBlockPHPIf('$this->_validate'.$this->getTable()->getClassName().'Input($'.$this->getTable()->getClassVariable().', '.$this->getFields($edit).')'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().'->commit();'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->post->setLocation(\''.$this->getListURL($edit).'\');'));
		$else = $if->addAlternate();
		$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->post->setLocation(\''.str_replace('${id}', '\'.$id.\'', $this->getCreateURL($edit)).'?error\', $input->serializePost());'));

	}

	/**
	 * Get list of fields to validate.
	 *
	 * @return string fields
	 */
	final public function getFields(DOMElement $element){
		$fields = array();
		$list = $element->getElementsByTagName('field');
		for($i = 0; $i < $list->length; $i++){
			$fields[] = $list->item($i)->getAttribute('name');
		}

		if(sizeof($fields) == 0){
			$fields = false;
		}
		$return = array();
		while(list(,$column) = $this->getTable()->eachColumn()){
			if(($fields === false || in_array($column->getName(), $fields)) && $column->isWritable()){
				$return[] = '\''.$column->getFieldReadableVariableName().'\'';
			}
		}
		return implode(', ', $return);
	}

	/**
	 * Check to see if a column is being validated.
	 *
	 * @param CodeGeneratorCodeBlockPHP $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true if validation occurs, else return false
	 */
	public function hasValidator(CodeGeneratorCodeBlockPHP $block, CodeGeneratorColumn $column){
		return $block->hasStatement('$input->validatePost(\''.$column->getFieldReadableVariableName().'\',');
	}

	/**
	 * Write input validation for field.
	 *
	 * @param CodeGeneratorCodeBlockPHP $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true on success, else return false
	 */
	public function writeValidator(CodeGeneratorCodeBlockPHP $block, CodeGeneratorColumn $column){
		$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$input->validatePost(\''.$column->getFieldReadableVariableName().'\', new '.$this->getValidatorClass($column).');'));
		return true;
	}

	/**
	 * Check to see if a column is being set.
	 *
	 * @param CodeGeneratorCodeBlockPHP $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true if set occurs, else return false
	 */
	public function hasSet(CodeGeneratorCodeBlockPHP $block, CodeGeneratorColumn $column){
		return $block->hasStatement('$'.$this->getTable()->getClassVariable().'->set'.$column->getFieldMethodName()) || $block->hasStatement('$input->isValidPost(\''.$column->getFieldReadableVariableName().'\')');
	}

	/**
	 * Write input set for field.
	 *
	 * @param CodeGeneratorCodeBlockPHP $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true on success, else return false
	 */
	public function writeSet(CodeGeneratorCodeBlockPHP $block, CodeGeneratorColumn $column){
		$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$input->isValidPost(\''.$column->getFieldReadableVariableName().'\')'));
		if($column->isForeignKey()){
			$data = 'new '.$column->getReferenceClassName().'($input->getPost(\''.$column->getFieldReadableVariableName().'\'))';
		} else {
			$data = '$input->getPost(\''.$column->getFieldReadableVariableName().'\')';
		}
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().'->set'.$column->getFieldMethodName().'('.$data.');'));
	}


	/**
	 * Get Field Validator class.
	 *
	 * get field validator class according to its datatype
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string validator class name
	 */
	public function getValidatorClass(CodeGeneratorColumn $column){
		switch ($column->getSmartType()){
			case CodeGeneratorColumn::TYPE_BOOLEAN:
				$class = 'InputValidatorEnum(\'true\',\'false\')';
				break;
			case CodeGeneratorColumn::TYPE_FLOAT:
				$class = 'InputValidatorIsFloat()';
				break;
			case CodeGeneratorColumn::TYPE_INTEGER:
				if($column->isForeignKey()){
					$class = 'InputValidatorModelExists(\''.$column->getReferenceClassName().'\')';
				} else {
					$class = 'InputValidatorInteger()';
				}
				break;
			case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP:
				$class = 'InputValidatorRegex(\'//\') /* Timestamp validation */';
				break;
			default:
				$class = 'InputValidatorNotEmpty()';
				break;
		}
		return $class;
	}

}
?>