<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator objects.
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
//***************** CodeGenerator DAO interface *******************//
//*****************************************************************//
/**
 * Code generator DAO interface.
 *
 * The Codegenerator dao interface is used the analyse
 * a datasource and return a {@link CodeGeneratorTable} instance
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 * @since Version 5.0
 * @author Steffen Sørensen <ss@corelib.org>
 */
interface DAO_CodeGenerator {


	//*****************************************************************//
	//************* CodeGenerator DAO interface methods ***************//
	//*****************************************************************//
	/**
	 * Analyse table.
	 *
	 * @param string $table table name
	 * @return CodeGeneratorTable
	 */
	public function analyseTable($table);

	/**
	 * Analyse table.
	 *
	 * @param string $view view name
	 * @return CodeGeneratorView
	 */
	public function analyseView($view);
}


//*****************************************************************//
//************ CodeGenerator Output and control class *************//
//*****************************************************************//
/**
 * CodeGenerator class.
 *
 * This class is able to gather information about
 * different code generators and make a resume of generators
 * in XML, this is also this class which does the generation workload.
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 * @since Version 5.0
 * @author Steffen Sørensen <ss@corelib.org>
 */
class CodeGenerator implements Output {


	//*****************************************************************//
	//***************** CodeGenerator class properties ****************//
	//*****************************************************************//
	/**
	 * CodeGenerator DAO instance.
	 *
	 * @var DAO_CodeGenerator
	 */
	private $dao = null;

	/**
	 * Know classes.
	 *
	 * @var array know classes
	 */
	private $classes = array();


	//*****************************************************************//
	//****************** CodeGenerator class methods ******************//
	//*****************************************************************//
	/**
	 * Create new codegenerator instance.
	 *
	 * Create a new instance of the CodeGenerator. if the codegenerator
	 * should do any work the $class parameter must be defined. when the
	 * CodeGenerator instance is created and the $class parameter is set
	 * the CodeGenerator will also gather all information and create all
	 * patches and or new files to make the code up-to-date.
	 *
	 * @uses CodeGenerator::_getDAO()
	 * @uses CodeGenerator::_loadObjects()
	 * @uses CodeGenerator::_generateCode()
	 * @param DOMElement $tree Manager class tree settings XML
	 * @param string $class classname
	 * @return void
	 */
	public function __construct(DOMElement $tree, $class=null){
		assert('is_null($class) || is_string($class)');

		$this->_getDAO();

		$this->_loadObjects($tree, $class);
		$this->_generateCode();
	}

	/**
	 * Apply code changes.
	 *
	 * Write all changes to disc.
	 *
	 * @uses CodeGenerator::$classes
	 * @uses CodeGeneratorPlugin::write()
	 * @return boolean true on success, else return false
	 */
	public function applyChanges(){
		foreach ($this->classes as $classname => $classinfo){
			foreach ($classinfo['generators'] as $generator){
				$generator->write();
			}
		}
		return true;
	}

	/**
	 * Create output XML.
	 *
	 * @uses CodeGeneratorPlugin::getXML()
	 * @uses CodeGenerator::$classes
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$codewriter = $xml->createElement('code-generator');
		foreach ($this->classes as $name => $classinfo){
			$actions = $codewriter->appendChild($xml->createElement('generator'));
			$actions->setAttribute('name', $name);
			foreach ($classinfo['generators'] as $generator){
				$actions->appendChild($generator->getXML($xml));
			}
		}
		return $codewriter;
	}


	/**
	 * Load generator objects.
	 *
	 * @param DOMElement $tree
	 * @uses CodeGenerator::_loadTable()
	 * @uses CodeGenerator::_loadView()
	 * @uses CodeGenerator::_loadGroup()
	 * @param string $object active object
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function _loadObjects(DOMElement $tree, $object=null){
		$xpath = new DOMXPath($tree->ownerDocument);
		if(!is_null($object)){
			$objects = $xpath->query('child::*[@name = \''.$object.'\']', $tree);
		} else {
			$objects = $xpath->query('child::*', $tree);
		}

		for ($i = 0; $i < $objects->length; $i++){

			if($objects->item($i)->getAttribute('prefix')){
				$prefix = $objects->item($i)->getAttribute('prefix');
			} else {
				$prefix = null;
			}

			switch ($objects->item($i)->nodeName){
				case 'table':
					$this->_loadTable($objects->item($i), $prefix);
					break;
				case 'view':
					$this->_loadView($objects->item($i), $prefix);
					break;
				case 'group':
					$this->_loadGroup($objects->item($i), $prefix);
					break;
			}
		}
		return true;
	}

	/**
	 * Load single table into the code generator.
	 *
	 * @uses CodeGeneratorPlugin::init()
	 * @uses DAO_CodeGenerator::analyseTable()
	 * @param DOMElement $table
	 * @param string $prefix path prefix
	 * @param string $group group
	 * @return boolean true on success, else return false;
	 * @internal
	 */
	private function _loadTable(DOMElement $table, $prefix=null, $group=null){
		$this->classes[$table->getAttribute('name')]['table'] = $this->dao->analyseTable($table->getAttribute('name'));

		$xpath = new DOMXPath($table->ownerDocument);
		$generators = $xpath->query('generator', $table);
		$this->classes[$table->getAttribute('name')]['generators'] = array();

		$mappings = $xpath->query('table-mapping', $table);
		for ($i = 0; $i < $mappings->length; $i++){
			if($reference_key = $mappings->item($i)->getAttribute('reference-key')){
				if($foreign_key = $mappings->item($i)->getAttribute('foreign-key')){
					$mapping = new CodeGeneratorTableMapping($this->dao->analyseTable($mappings->item($i)->getAttribute('name')), $reference_key, $foreign_key);
					$this->classes[$table->getAttribute('name')]['table']->addTableMapping($mapping);
				} else {
					$mapping = new CodeGeneratorTableMapping($this->dao->analyseTable($mappings->item($i)->getAttribute('name')), $reference_key);
					$this->classes[$table->getAttribute('name')]['table']->addTableMapping($mapping);
				}
			}
		}

		for ($i = 0; $i < $generators->length; $i++){
			$class = $generators->item($i)->getAttribute('name');
			$class = new $class($this->classes[$table->getAttribute('name')]['table'], $generators->item($i), $prefix, $group);
			$class->init();
			// $generators = $xpath->query('generator', $table);

  			$this->classes[$table->getAttribute('name')]['generators'][] = $class;
		}
		return true;
	}

	/**
	 * Load view into the codegenerator.
	 *
	 * @todo XXX Implement the ability to read views
	 *
	 * @param DOMElement $view
	 * @param string $group
	 * @return return boolean true on success, else return false
	 * @internal
	 */
	private function _loadView(DOMElement $view, $prefix=null, $group=null){
		$this->classes[$view->getAttribute('name')]['table'] = $this->dao->analyseView($view->getAttribute('name'));

		$xpath = new DOMXPath($view->ownerDocument);
		$generators = $xpath->query('generator', $view);
		$this->classes[$view->getAttribute('name')]['generators'] = array();

		for ($i = 0; $i < $generators->length; $i++){
			$class = $generators->item($i)->getAttribute('name');
			$class = new $class($this->classes[$view->getAttribute('name')]['table'], $generators->item($i), $prefix, $group);
			$class->init();
  			$this->classes[$view->getAttribute('name')]['generators'][] = $class;
		}
	}

	/**
	 * Load group into the codegenerator.
	 *
	 * @uses CodeGenerator::_loadTable()
	 * @uses CodeGenerator::_loadView()
	 * @param DOMElement $group
	 * @return return boolean true on success, else return false
	 * @internal
	 */
	private function _loadGroup(DOMElement $group){
		if($group->getAttribute('prefix')){
			$prefix = $group->getAttribute('prefix');
		} else {
			$prefix = null;
		}

		for ($i = 0; $i < $group->childNodes->length; $i++){
			switch ($group->childNodes->item($i)->nodeName){
				case 'table':
					$this->_loadTable($group->childNodes->item($i), $prefix, $group->getAttribute('name'));
					break;
				case 'view':
					$this->_loadView($group->childNodes->item($i), $group->getAttribute('name'));
					break;
			}
		}
		return true;
	}

	/**
	 * Generate code.
	 *
	 * Apply all pending patches, and create all pending files
	 *
	 * @uses CodeGenerator::$classes
	 * @uses CodeGeneratorFile::generate()
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function _generateCode(){
		foreach ($this->classes as $classname => $classinfo){
			foreach ($classinfo['generators'] as $generator){
				$generator->generate();
			}
		}
	}

	/**
	 * Get Current DAO object instance
	 *
	 * @uses CodeGenerator::$dao
	 * @uses Database::getDAO()
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('CodeGenerator');
		}
		return true;
	}
}
?>