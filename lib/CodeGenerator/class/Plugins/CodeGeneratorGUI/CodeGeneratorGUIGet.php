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
//****************** CodeGeneratorGUIGet class ********************//
//*****************************************************************//
/**
 * CodeGenerator gui get file
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorGUIGet extends CodeGeneratorGUIFilePHP {


	//*****************************************************************//
	//***************** CodeGeneratorGUIGet methods *******************//
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

		$prefix .= 'lib/http/get/';

		if(!is_null($group)){
			$prefix .= $this->_getReadableGroup($group).'/';
		}

		$this->_setFilename($prefix.$this->getTable()->getTableReadableVariableName().'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Get.php.generator');
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

		if($block = $this->_getCodeBlock($this->content, 'Interface get methods')){
			$xpath = new DOMXPath($this->settings->ownerDocument);

			$list = $xpath->query('edit|list|create|view|delete', $this->settings);
			if($list->length > 0){
				for($i = 0; $i < $list->length; $i++){
					$method = $list->item($i)->getAttribute('method');
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

						switch($list->item($i)->nodeName){
							case 'list':
								$this->writeListMethod($method, $list->item($i));
								break;
							case 'create':
								$this->writeCreateMethod($method, $list->item($i));
								break;
							case 'edit':
								$this->writeEditMethod($method, $list->item($i));
								break;
							case 'view':
								$this->writeViewMethod($method, $list->item($i));
								break;
							case 'delete':
								$this->writeDeleteMethod($method, $list->item($i));
								break;
						}
					}
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
		if(preg_match_all('/abstract class (.*?PageGet|.*?Page) extends .*?Page.*?/', $abstracts, $match)){
			foreach($match[1] as $class){
				if(preg_match('/.*?PageGet/', $class)){
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
	 * Write list method to template.
	 *
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $list
	 * @return void
	 */
	public function writeListMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $list){
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$list = new '.$this->getTable()->getClassName().'List();'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$list->setLimit('.$list->getAttribute('page-limit').');'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$list->setPage($this->getPagingPage());'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->addContent($list);'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->addTemplate(\''.$list->getAttribute('gui-xsl-filename').'\');'));
	}

	/**
	 * Write create method to template.
	 *
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $create
	 * @return void
	 */
	public function writeCreateMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $create){
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->addTemplate(\''.$create->getAttribute('gui-xsl-filename').'\');'));
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
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().' = new '.$this->getTable()->getClassName().'($id);'));

		$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$this->getTable()->getClassVariable().'->read()'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->addTemplate(\''.$edit->getAttribute('gui-xsl-filename').'\');'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->addContent($'.$this->getTable()->getClassVariable().');'));
		$else = $if->addAlternate();
		$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->addTemplate(\'share/xsl/pages/404.xsl\');'));
	}

	/**
	 * Write delete method to template.
	 *
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $delete
	 * @return void
	 */
	public function writeDeleteMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $delete){
		$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$id'));
		$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().' = new '.$this->getTable()->getClassName().'($id);'));

		$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$this->getTable()->getClassVariable().'->read()'));
		$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().'->delete();'));

		if($url = $this->getListURL($delete)){
			$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->setLocation(\''.$url.'\');'));
		} else {
			$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('echo \'Don\\\'t know what to do now, exiting.....\';'));
		}

		$else = $if->addAlternate();
		$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->xsl->addTemplate(\'share/xsl/pages/404.xsl\');'));
	}

	/**
	 * Write view method to template.
	 *
	 * @uses CodeGeneratorGUIGet::writeEditMethod()
	 * @param CodeGeneratorCodeBlockPHPClassMethod $method
	 * @param DOMElement $view
	 * @return void
	 */
	public function writeViewMethod(CodeGeneratorCodeBlockPHPClassMethod $method, DOMElement $view){
		$this->writeEditMethod($method, $view);
	}
}
?>