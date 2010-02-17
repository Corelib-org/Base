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
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('COMMAND_PATCH')){
	/**
	 * Path to patch command.
	 *
	 * @var string
	 */
	define('COMMAND_PATCH', '/usr/bin/patch');
}
if(!defined('COMMAND_DIFF')){
	/**
	 * Path to diff command.
	 *
	 * @var string
	 */
	define('COMMAND_DIFF', '/usr/bin/diff');
}


//*****************************************************************//
//******************* CodeGeneratorFile class *********************//
//*****************************************************************//
/**
 * CodeGenerator File base class.
 *
 * All files generated by the filemanager should extend this class
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 */
abstract class CodeGeneratorFile implements Output {


	//*****************************************************************//
	//************** CodeGeneratorFile class properties ***************//
	//*****************************************************************//
	/**
	 * Analyzed table.
	 *
	 * @var CodeGeneratorTable
	 */
	private $table = null;

	/**
	 * Target filename.
	 *
	 * @var string filename
	 */
	private $filename = null;

	/**
	 * @var DOMElement settings
	 */
	protected $settings = null;

	/**
	 * @var string write results
	 */
	private $write_result = null;

	/**
	 * @var string group
	 */
	private $group = null;

	/**
	 * @var string content to write
	 */
	protected $content = null;

	//*****************************************************************//
	//************** CodeGeneratorFile abstract methods ***************//
	//*****************************************************************//
	/**
	 * Generate code.
	 *
	 * @return boolean true on success, else return false
	 */
	abstract public function generate();


	//*****************************************************************//
	//******************** CodeGeneratorFile methods ******************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @uses CodeGeneratorFile::$table
	 * @uses CodeGeneratorFile::$path
	 * @param string $path path
	 * @param CodeGeneratorTable $table
	 * @return void
	 */
	public function __construct(CodeGeneratorTable $table, DOMElement $settings=null, $prefix=null, $group=null){
		$this->table = $table;
		$this->settings = $settings;
		$this->group = $group;
	}

	/**
	 * Create output XML.
	 *
	 * @uses CodeGeneratorFile::getFilename()
	 * @uses CodeGeneratorFile::createPatch()
	 * @uses CodeGeneratorFile::$content
	 * @uses CodeGeneratorFile::$write_result
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$action = $xml->createElement('file');
		$action->setAttribute('filename', $this->getFilename());

		if(is_file($this->getFilename())){
			if(md5_file($this->getFilename()) == md5($this->content)){
				$action->setAttribute('action', 'none');
			} else {
				$action->setAttribute('action', 'patch');
				if(!is_null($this->write_result)){
					$action->appendChild($xml->createTextNode($this->write_result));
				} else {
					$patch = htmlspecialchars($this->createPatch());
					$patch = preg_replace('/^(-{1}(\s*|\?).*?)$/m', '<span class="GeneratorLineRemove">\\1</span>', $patch);
					$patch = preg_replace('/^(\+{1}(\s*|\?).*?)$/m', '<span class="GeneratorLineAdd">\\1</span>', $patch);
					$action->appendChild($xml->createTextNode($patch));
				}
			}
		} else {
			$action->setAttribute('action', 'create');
		}
		return $action;
	}

	/**
	 * Get target filename and path.
	 *
	 * @uses CodeGeneratorFile::$path
	 * @uses CodeGeneratorFile::$filename
	 * @return string absolute filename and path
	 */
	public function getFilename(){
		return $this->filename;
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
	 * Get genertor settings.
	 *
	 * @uses CodeGeneratorFile::$group
	 * @return string group name
	 */
	public function getSettings(){
		return $this->settings;
	}

	/**
	 * Get genertor group.
	 *
	 * @uses CodeGeneratorFile::$settings
	 * @return DOMElement
	 */
	public function getGroup(){
		return $this->group;
	}

	/**
	 * Write class name to file/template.
	 *
	 * Replace all occurences of ${classname} with the actual class name
	 *
	 * @uses CodeGeneratorFile::$table
	 * @uses CodeGeneratorTable::getClassName();
	 * @param string $content file content
	 * @return boolean true
	 * @internal
	 */
	private function _writeClassName(&$content){
		$content = str_replace('${classname}', $this->table->getClassName(), $content);
		return true;
	}

	/**
	 * Write table name to file/template.
	 *
	 * Replace all occurences of ${tablename} with the actual table name
	 *
	 * @uses CodeGeneratorFile::$table
	 * @uses CodeGeneratorTable::getName();
	 * @param string $content file content
	 * @return boolean true
	 * @internal
	 */
	private function _writeTableName(&$content){
		$content = str_replace('${tablename}', $this->table->getName(), $content);
		return true;
	}

	/**
	 * Write class var name to file/template.
	 *
	 * Replace all occurences of ${classvar} with the actual class var name
	 *
	 * @uses CodeGeneratorFile::$table
	 * @uses CodeGeneratorTable::getClassVariable();
	 * @param string $content file content
	 * @return boolean true
	 * @internal
	 */
	private function _writeClassVar(&$content){
		$content = str_replace('${classvar}', $this->table->getClassVariable(), $content);
	}

	/**
	 * Write group name to file/template.
	 *
	 * Replace all occurences of ${groupname} with the actual group name
	 *
	 * @uses CodeGeneratorFile::$table
	 * @uses CodeGeneratorFile::$group
	 * @uses CodeGeneratorTable::getClassVariable();
	 * @param string $content file content
	 * @return boolean true
	 * @internal
	 */
	private function _writeGroupName(&$content){
		if(is_null($this->group)){
			$content = str_replace('${groupname}', $this->table->getClassVariable(), $content);
		} else {
			$content = str_replace('${groupname}', $this->group, $content);
		}
	}

	/**
	 * Set filename.
	 *
	 * @uses CodeGeneratorFile::$filename
	 * @param string $filename
	 * @return return boolean true on success, else return false
	 */
	protected function _setFilename($filename){
		 $this->filename = str_replace('//', '/', $filename);
		 return true;
	}

	/**
	 * Load template.
	 *
	 * If target file does'nt exist, the code generator will
	 * try to read content from the given template, otherwise
	 * it will read the content from the existing file.
	 *
	 * @uses CodeGeneratorFile::_writeClassVar()
	 * @uses CodeGeneratorFile::_writeTableName()
	 * @uses CodeGeneratorFile::_writeClassName()
	 * @uses CodeGeneratorFile::getFilename()
	 * @uses CodeGeneratorFile::$content
	 * @param string $template filename
	 * @return return boolean true on success, else return false;
	 */
	protected function _loadContent($template){
		if(assert('is_string($template)') && assert('is_file($template)')){
			if(is_file($this->getFilename())){
				$this->content = file_get_contents($this->getFilename());
			} else {
				$this->content = file_get_contents($template);
				$this->_writeClassVar($this->content);
				$this->_writeTableName($this->content);
				$this->_writeClassName($this->content);
				$this->_writeGroupName($this->content);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get template codeblock.
	 *
	 * Get a block of code generator written code or empty
	 * place to write code into. a code block could look like
	 * this:
	 *
	 * $prefix $block $suffix
	 *  ... generated code goes here ...
	 * $prefix $block end $suffix
	 *
	 * @param string $source
	 * @param string $block block name
	 * @param string $prefix
	 * @param string $suffix
	 * @return mixed CodeGeneratorCodeBlock code block if block was found, else return false
	 */
	protected function _getCodeBlock(&$source, $block, $prefix, $suffix){
		$block = new CodeGeneratorCodeBlock($source, $block, $prefix='/*', $suffix='*/' );
		if($block->exist()){
			return $block;
		} else {
			return false;
		}
	}

	/**
	 * Write code block to template.
	 *
	 * write the generated code to codeblock
	 *
	 * @param string $source
	 * @param CodeGeneratorCodeBlock $block
	 * @return boolean true on success, else return false
	 */
	protected function _writeCodeBlock(&$source, CodeGeneratorCodeBlock $block){
		if($oblock = $this->_getCodeBlock($source, $block->getName(), $block->getPrefix(), $block->getSuffix())){
			$source = str_replace($oblock->getSource(), $block->getSource(), $source);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write changes.
	 *
	 * @uses CodeGeneratorFile::generate()
	 * @uses CodeGeneratorFile::$content
	 * @uses CodeGeneratorFile::getFilename()
	 * @uses CodeGeneratorFile::createPatch()
	 * @uses CodeGeneratorFile::$write_result
	 * @uses COMMAND_PATCH
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function write(){
		$this->generate();

		$this->content = trim($this->content);
		if(!is_dir(dirname($this->getFilename()))){
			mkdir(dirname($this->getFilename()), 0777, true);
		}
		if(is_file($this->getFilename())){
			if(md5_file($this->getFilename()) != md5($this->content)){
				$tempname = tempnam('var/tmp', 'diff');
				file_put_contents($tempname, $this->createPatch());
				chmod($tempname, 0666);
				$command = COMMAND_PATCH.' '.$this->getFilename().' -i '.$tempname.' 2>&1';
				exec($command, $result);
				$this->write_result = implode("\n", $result);
				unlink($tempname);
			}
		} else {
			file_put_contents($this->getFilename(), $this->content);
		}
		return true;
	}

	/**
	 * Create patch.
	 *
	 * Create a patch based on the old and new file.
	 *
	 * @uses CodeGeneratorFile::$content
	 * @uses CodeGeneratorFile::getFilename()
	 * @uses COMMAND_DIFF
	 * @return string patch
	 * @internal
	 */
	private function createPatch(){
		$tempfile = tempnam('var/tmp', 'diff');
		file_put_contents($tempfile, $this->content);
		$diff = COMMAND_DIFF.' -usN '.str_replace('//', '/', $this->getFilename()).' '.$tempfile;
		$diff = trim(`$diff`);
		unlink($tempfile);
		return $diff;
	}

	protected function _getReadableGroup($group){
		return strtolower(preg_replace('/([a-z])([A-Z])/', '\\1-\\2', $group));
	}
}
?>