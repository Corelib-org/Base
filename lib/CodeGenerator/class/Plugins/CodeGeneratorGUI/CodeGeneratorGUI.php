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
//******************** CodeGeneratorGUI class *********************//
//*****************************************************************//
/**
 * CodeGenerator model plugin.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorGUI extends CodeGeneratorPlugin {


	//*****************************************************************//
	//****************** CodeGeneratorGUI methods *********************//
	//*****************************************************************//
	/**
	 * Init plugin.
	 *
	 * Initiate plugin and add {@link CodeGeneratorModelFile}
	 * and {@link CodeGeneratorModelFileDAOMySQLi} to code genrator queue
	 *
	 * @return void
	 */
	public function init(){
		$xpath = new DOMXPath($this->settings->ownerDocument);

		if($this->settings->getElementsByTagName('layout')->length > 0){
			$layout = $this->settings->getElementsByTagName('layout')->item(0);
			if(strlen(trim($layout->getAttribute('name'))) <= 0){
				$layout->setAttribute('name', 'CodeGeneratorGUILayout');
			}
			$this->_addFile($this->_createFileInstance($layout->getAttribute('name'), $layout));
		}

		// Instanciate all types of lists and set default settings.
		$list = $xpath->query('list', $this->settings);
		if($list->length > 0){
			for($i = 0; $i < $list->length; $i++){
				if(strlen(trim($list->item($i)->getAttribute('name'))) <= 0){
					$list->item($i)->setAttribute('name', 'CodeGeneratorGUIList');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-mode'))) <= 0){
					$list->item($i)->setAttribute('xsl-mode', 'xhtml-list');
				}
				if(strlen(trim($list->item($i)->getAttribute('method'))) <= 0){
					$list->item($i)->setAttribute('method', $this->getTable()->getClassName().'List');
				}
				if(strlen(trim($list->item($i)->getAttribute('action'))) <= 0){
					$list->item($i)->setAttribute('action', 'default');
				}
				if(strlen(trim($list->item($i)->getAttribute('layout'))) <= 0){
					$list->item($i)->setAttribute('layout', 'base/layouts/default.xsl');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-layout-mode'))) <= 0){
					$list->item($i)->setAttribute('xsl-layout-mode', 'xhtml-content');
				}
				if(strlen(trim($list->item($i)->getAttribute('page-limit'))) <= 0){
					$list->item($i)->setAttribute('page-limit', '20');
				}
				if(strlen(trim($list->item($i)->getAttribute('draw-actions'))) <= 0){
					$list->item($i)->setAttribute('draw-actions', 'true');
				}
				$file = $this->_addFile($this->_createFileInstance($list->item($i)->getAttribute('name'), $list->item($i)));
				$list->item($i)->setAttribute('gui-url', $this->createURL($file, '/'));
			}
		}

		// Instanciate all types of views and set default settings.
		$list = $xpath->query('view', $this->settings);
		if($list->length > 0){
			for($i = 0; $i < $list->length; $i++){
				if(strlen(trim($list->item($i)->getAttribute('name'))) <= 0){
					$list->item($i)->setAttribute('name', 'CodeGeneratorGUIView');
				}
				if(strlen(trim($list->item($i)->getAttribute('method'))) <= 0){
					$list->item($i)->setAttribute('method', 'view');
				}
				if(strlen(trim($list->item($i)->getAttribute('action'))) <= 0){
					$list->item($i)->setAttribute('action', 'default');
				}
				if(strlen(trim($list->item($i)->getAttribute('layout'))) <= 0){
					$list->item($i)->setAttribute('layout', 'base/layouts/default.xsl');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-layout-mode'))) <= 0){
					$list->item($i)->setAttribute('xsl-layout-mode', 'xhtml-content');
				}
				$file = $this->_addFile($this->_createFileInstance($list->item($i)->getAttribute('name'), $list->item($i)));

				if($list->item($i)->getAttribute('action') == 'default'){
					$action = 'view';
				} else {
					$action = 'view/'.$list->item($i)->getAttribute('action');
				}
				$list->item($i)->setAttribute('gui-url', $this->createURL($file, '/${id}/'.$action.'/'));
			}
		}

		// Instanciate all types of edits and set default settings.
		$list = $xpath->query('edit', $this->settings);
		if($list->length > 0){
			for($i = 0; $i < $list->length; $i++){
				if(strlen(trim($list->item($i)->getAttribute('name'))) <= 0){
					$list->item($i)->setAttribute('name', 'CodeGeneratorGUIEdit');
				}
				if(strlen(trim($list->item($i)->getAttribute('method'))) <= 0){
					$list->item($i)->setAttribute('method', 'edit');
				}
				if(strlen(trim($list->item($i)->getAttribute('action'))) <= 0){
					$list->item($i)->setAttribute('action', 'default');
				}
				if(strlen(trim($list->item($i)->getAttribute('layout'))) <= 0){
					$list->item($i)->setAttribute('layout', 'base/layouts/default.xsl');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-layout-mode'))) <= 0){
					$list->item($i)->setAttribute('xsl-layout-mode', 'xhtml-content');
				}
				$file = $this->_addFile($this->_createFileInstance($list->item($i)->getAttribute('name'), $list->item($i)));

				if($list->item($i)->getAttribute('action') == 'default'){
					$action = 'edit';
				} else {
					$action = 'edit/'.$list->item($i)->getAttribute('action');
				}
				$list->item($i)->setAttribute('gui-url', $this->createURL($file, '/${id}/'.$action.'/'));
			}
		}

		// Instanciate all types of create and set default settings.
		$list = $xpath->query('create', $this->settings);
		if($list->length > 0){
			for($i = 0; $i < $list->length; $i++){
				if(strlen(trim($list->item($i)->getAttribute('name'))) <= 0){
					$list->item($i)->setAttribute('name', 'CodeGeneratorGUICreate');
				}
				if(strlen(trim($list->item($i)->getAttribute('method'))) <= 0){
					$list->item($i)->setAttribute('method', 'create');
				}
				if(strlen(trim($list->item($i)->getAttribute('action'))) <= 0){
					$list->item($i)->setAttribute('action', 'default');
				}
				if(strlen(trim($list->item($i)->getAttribute('layout'))) <= 0){
					$list->item($i)->setAttribute('layout', 'base/layouts/default.xsl');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-layout-mode'))) <= 0){
					$list->item($i)->setAttribute('xsl-layout-mode', 'xhtml-content');
				}
				$file = $this->_addFile($this->_createFileInstance($list->item($i)->getAttribute('name'), $list->item($i)));

				if($list->item($i)->getAttribute('action') == 'default'){
					$action = 'create';
				} else {
					$action = 'create/'.$list->item($i)->getAttribute('action');
				}
				$list->item($i)->setAttribute('gui-url', $this->createURL($file, '/'.$action.'/'));
			}
		}

		$list = $xpath->query('delete', $this->settings);
		if($list->length > 0){
			if(strlen(trim($list->item(0)->getAttribute('method'))) <= 0){
				$list->item(0)->setAttribute('method', 'delete');
			}

			$list->item(0)->setAttribute('gui-url', $this->createURL($file, '/${id}/delete/'));

		}

		$get = $this->_addFile($this->_createFileInstance('CodeGeneratorGUIGet', $this->settings));
		$post = $this->_addFile($this->_createFileInstance('CodeGeneratorGUIPost', $this->settings));

		$this->addLookRegistryFiles($get, $post);

	}

	/**
	 * Add gui registry files to generator queue.
	 *
	 * This method may be overwritten to change how the files are added to the registry.
	 *
	 * @return void
	 */
	public function addLookRegistryFiles(CodeGeneratorGUIGet $get, CodeGeneratorGUIPost $post){
		$file = $this->_addFile($this->_createFileInstance('CodeGeneratorGUIGetRegistry', $this->settings));
		$file->setGetFile($get);

		$file = $this->_addFile($this->_createFileInstance('CodeGeneratorGUIPostRegistry', $this->settings));
		$file->setPostFile($post);
	}

	/**
	 * Convert class url name to plural.
	 *
	 * @param string $url
	 * @return string new url
	 */
	public function createURL($file, $url){
		$base = dirname(preg_replace('/^.*pages\/(.*)$/', '\\1', $file->getFilename()));
		$base = explode('/', $base);
		$base[sizeof($base) - 1] = $base[sizeof($base) - 1].'s';
		$base = implode($base, '/');
		return $base.$url;
	}
}


//*****************************************************************//
//**************** CodeGeneratorGUIFileXSL class ******************//
//*****************************************************************//
/**
 * CodeGenerator gui file xsl base class.
 *
 * GUI support class with name helping functions
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorGUIFileXSL extends CodeGeneratorFileXSL {


	//*****************************************************************//
	//************** CodeGeneratorGUIFileXSL methods ******************//
	//*****************************************************************//
	/**
	 * Get field edit id.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field edit id
	 */
	public function getFieldEditID(CodeGeneratorColumn $column){
		return $this->getTable()->getTableReadableVariableName().'-edit-'.$column->getFieldReadableVariableName();
	}

	/**
	 * Get field edit name.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field edit name
	 */
	public function getFieldEditName(CodeGeneratorColumn $column){
		return $column->getFieldReadableVariableName();
	}

	/**
	 * Get field view name.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string field edit name
	 */
	public function getFieldViewName(){
		return $this->getTable()->getTableReadableVariableName().'-view-field';
	}

	/**
	 * Set filename.
	 *
	 * @see CodeGeneratorFile::_setFilename()
	 */
	protected function _setFilename($filename){
		$this->settings->setAttribute('gui-xsl-filename', preg_replace('/^share\/xsl\//', '', $filename));
		parent::_setFilename($filename);
	}
}

//*****************************************************************//
//**************** CodeGeneratorGUIFilePHP class ******************//
//*****************************************************************//
/**
 * CodeGenerator gui file php base class.
 *
 * GUI support class with name helping functions
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorGUIFilePHP extends CodeGeneratorFilePHP {


	//*****************************************************************//
	//************** CodeGeneratorGUIFilePHP methods ******************//
	//*****************************************************************//
	/**
	 * Get list url.
	 *
	 * @param DOMElement $element
	 * @return unknown_type
	 */
	public function getListURL(DOMElement $element){
		$xpath = new DOMXPath($this->settings->ownerDocument);
		$lists = $xpath->query('list', $element->parentNode);
		if($lists->length > 0){
			return $lists->item(0)->getAttribute('gui-url');
		} else {
			return false;
		}
	}

	/**
	 * Get create url.
	 *
	 * @param DOMElement $element
	 * @return string create url
	 */
	public function getCreateURL(DOMElement $element){
		return $element->getAttribute('gui-url');
	}

	/**
	 * Write get class name.
	 *
	 * Replaces all occurences of ${pageclassname}
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	public function writePageClassName(&$content){
		$content = str_replace('${pageclassname}', 'WebPage', $content);
		return true;
	}
}
?>