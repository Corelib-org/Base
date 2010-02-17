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
//****************** CodeGenrator Plugin class ********************//
//*****************************************************************//
/**
 * CodeGenerator plugin.
 *
 * The code generator represents a plugin.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorPlugin implements Output {


	//*****************************************************************//
	//*************** CodeGenrator Plugin properties ******************//
	//*****************************************************************//
	/**
	 * Path prefix.
	 *
	 * @var string
	 * @internal
	 */
	private $prefix = null;

	/**
	 * @var CodeGeneratorTable
	 * @internal
	 */
	private $table = null;

	/**
	 * File queue.
	 *
	 * @var array
	 * @internal
	 */
	private $files = array();

	/**
	 * @var string write result
	 */
	private $write_result = null;

	/**
	 * @var DOMElement settings
	 */
	protected $settings = null;


	//*****************************************************************//
	//************ CodeGenrator Plugin abstract methods ***************//
	//*****************************************************************//
	/**
	 * Initiate plugin.
	 *
	 * @return boolean true on success, else return false.
	 */
	abstract public function init();


	//*****************************************************************//
	//**************** CodeGenrator Plugin methods ********************//
	//*****************************************************************//
	/**
	 * Create new plugin instance.
	 *
	 * @uses CodeGeneratorPlugin::$prefix
	 * @uses CodeGeneratorPlugin::$group
	 * @uses CodeGeneratorPlugin::$table
	 * @uses CodeGeneratorPlugin::$settings
	 * @param CodeGeneratorTable $table
	 * @param DOMElement $settings
	 * @param string $prefix
	 * @param string $group
	 * @return void
	 */
	public function __construct(CodeGeneratorTable $table, DOMElement $settings=null, $prefix=null, $group=null){
		if(!is_null($prefix)){
			$this->prefix = Manager::parseConstantTags($prefix).'/';
		}
		$this->group = $group;
		$this->table = $table;
		$this->settings = $settings;
	}

	/**
	 * Generate code for each file.
	 *
	 * @uses CodeGeneratorPlugin::$files
	 * @uses CodeGeneratorFile::generate()
	 * @return boolean true on success, else return false
	 */
	public function generate(){
		foreach ($this->files as $file){
			$file->generate();
		}
	}

	/**
	 * Write code for each file.
	 *
	 * @uses CodeGeneratorPlugin::$files
	 * @uses CodeGeneratorFile::write()
	 * @return boolean true on success, else return false
	 */
	public function write(){
		foreach ($this->files as $file){
			$file->write();
		}
	}

	/**
	 * Create new instance of a file object.
	 *
	 * @uses CodeGeneratorPlugin::$table
	 * @uses CodeGeneratorPlugin::$prefix
	 * @uses CodeGeneratorPlugin::$group
	 * @param string $class classname
	 * @param DOMElement $settings
	 * @return CodeGeneratorFile
	 */
	protected function _createFileInstance($class, DOMElement $settings=null){
		return new $class($this->table, $settings, $this->prefix, $this->group);
	}

	/**
	 * Add file.
	 *
	 * @uses CodeGeneratorPlugin::$files
	 * @see CodeGeneratorPlugin::_createFileInstance()
	 * @param CodeGeneratorFile $file
	 * @return CodeGeneratorFile
	 */
	protected function _addFile(CodeGeneratorFile $file){
		$this->files[] = $file;
		return $file;
	}

	/**
	 * Get table instance.
	 *
	 * @uses CodeGeneratorFile::$table
	 * @return CodeGeneratorTable
	 */
	public function getTable(){
		return $this->table;
	}

	/**
	 * Get XML.
	 *
	 * @uses CodeGeneratorPlugin::$files
	 * @uses CodeGeneratorFile::geXML()
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$files = $xml->createElement('files');
		foreach ($this->files as $file){
			$files->appendChild($file->getXML($xml));
		}
		return $files;
	}
}
?>