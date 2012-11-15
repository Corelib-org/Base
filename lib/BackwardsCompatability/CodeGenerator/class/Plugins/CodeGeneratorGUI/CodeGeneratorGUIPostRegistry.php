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
//************* CodeGeneratorGUIPostRegistry class ****************//
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
class CodeGeneratorGUIPostRegistry extends CodeGeneratorGUIFilePHP {

	/**
	 * @var CodeGeneratorGUIPost
	 */
	private $post = null;

	//*****************************************************************//
	//************ CodeGeneratorGUIPostRegistry methods ***************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorGUIPostRegistry.
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

		$this->_setFilename('etc/post.php');
		$this->_loadContent('etc/post.php');
	}

	/**
	 * Set GUI Post file instance.
	 *
	 * @param CodeGeneratorGUIPost $post
	 * @return void
	 */
	public function setPostFile(CodeGeneratorGUIPost $post){
		$this->post = $post;
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$xpath = new DOMXPath($this->settings->ownerDocument);
		$list = $xpath->query('edit|create', $this->settings);

		$this->content = preg_replace('/\?\>\s*$/', '', $this->content);

		if($list->length > 0){
			for($i = 0; $i < $list->length; $i++){
				if(in_array($list->item($i)->nodeName, array('edit'))){
					$title = '$pages[] = array(';
					$type = '\'type\' => \'regex\','."\n";
					$expr = str_replace('/', '\/', '/'.str_replace('${id}', '([0-9]+)', $list->item($i)->getAttribute('gui-url')));
					$expr = str_repeat(' ', strlen($title)).'\'expr\' => \'/^'.$expr.'$/\','."\n";
					$exec = str_repeat(' ', strlen($title)).'\'exec\' => \''.$list->item($i)->getAttribute('method').'(\\\\1)\','."\n";
					$page = str_repeat(' ', strlen($title)).'\'page\' => \''.$this->post->getFilename().'\');'."\n\n";
					if(!strstr($this->content, $expr)){
						$this->content .= $title.$type.$expr.$exec.$page;
					}
				} else {
					$title = '$pages[\'/'.$list->item($i)->getAttribute('gui-url').'\'] = array(';
					$page = '\'page\' => \''.$this->post->getFilename().'\','."\n";
					$exec = str_repeat(' ', strlen($title)).'\'exec\' => \''.$list->item($i)->getAttribute('method').'\');'."\n\n";
					if(!strstr($this->content, $title)){
						$this->content .= $title.$page.$exec;
					}
				}
			}
		}
		$this->content .= '?>';
		$this->content = trim($this->content);
	}
}
?>