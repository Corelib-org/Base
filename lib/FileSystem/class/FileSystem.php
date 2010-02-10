<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Filesystem Classes.
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
 * @subpackage FileSystem
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
if(!defined('FILE_SYSTEM_FILE_DIR')){
	/**
	 * Define the default storage location for filesystem.
	 *
	 * @var string directory to store files.
	 * @since Version 5.0
	 */
	define('FILE_SYSTEM_FILE_DIR', 'var/db/filesystem/');
}

if(!defined('FILE_SYSTEM_GET_FILE_HANDLER')){
	/**
	 * Define the default storage location for filesystem.
	 *
	 * @since Version 5.0
	 * @var string directory to store files.
	 * @internal
	 */
	define('FILE_SYSTEM_GET_FILE_HANDLER', CORELIB.'Base/lib/FileSystem/http/get/filesystem.php');
}


//*****************************************************************//
//*********************** Define constants ************************//
//*****************************************************************//
/**
 * Define template engine name.
 *
 * Assign the template engine name for which should
 * be used when the FileSystem tries to serve a file.
 *
 * @var string class name {@link FileSystemTemplateEngine}
 * @since Version 5.0
 */
define('FILE_SYSTEM_TEMPLATE_ENGINE', 'FileSystemTemplateEngine');


//*****************************************************************//
//********** FileSystemTemplateEngine template engine *************//
//*****************************************************************//
/**
 * Filesystem dummy template engine.
 *
 * A dummy template engine which does not implment any output, it simply
 * serves as a loader before writing the actual file output to the client.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage FileSystem
 * @category corelib
 * @since Version 5.0
 * @internal
 */
class FileSystemTemplateEngine extends PageFactoryTemplateEngine {


	//*****************************************************************//
	//************ FileSystemTemplateEngine Class Methods *************//
	//*****************************************************************//
	/**
	 * Page draw method.
	 *
	 * This method is a dummy and noes nothing.
	 *
	 * @see PageFactoryTemplateEngine::draw()
	 * @since Version 5.0
	 * @return string empty string
	 * @internal
	 */
	public function draw(){ return ''; }

	/**
	 * Add page settings.
	 *
	 * This method is a dummy and noes nothing.
	 *
	 * @since Version 5.0
	 * @see PageFactoryTemplateEngine::addPageSettings()
	 */
	public function addPageSettings(Output $content){ }

	/**
	 * Add page content.
	 *
	 * This method is a dummy and noes nothing.
	 *
	 * @since Version 5.0
	 * @see PageFactoryTemplateEngine::addPageContent()
	 */
	public function addPageContent(Output $settings){ }

	/**
	 * Get supported template definition.
	 *
	 * @see PageFactoryTemplateEngine::getSupportedTemplateDefinition()
	 * @since Version 5.0
	 * @return string this class name {@link FileSystemTemplateEngine}
	 * @internal
	 */
	public function getSupportedTemplateDefinition(){ return __CLASS__; }
}


//*****************************************************************//
//************ PageFactoryFileSystemTemplate template *************//
//*****************************************************************//
/**
 * Dummy Template
 *
 * This is a dummy template which is used with
 * {@link FileSystemTemplateEngine} to send the correct
 * headers when serving a file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @package Base
 * @subpackage FileSystem
 * @category corelib
 * @internal
 */
class PageFactoryFileSystemTemplate extends PageFactoryWebAbstractTemplate {


	//*****************************************************************//
	//******** PageFactoryFileSystemTemplate Class Properties *********//
	//*****************************************************************//
	/**
	 * @var string content disposition method
	 * @internal
	 */
	private $content_disposition = 'inline';

	/**
	 * @var string content filename
	 * @internal
	 */
	private $content_filename = null;


	//*****************************************************************//
	//******** PageFactoryFileSystemTemplate Class Methods ************//
	//*****************************************************************//
	/**
	 * Get supported Template engine.
	 *
	 * @see PageFactoryTemplate::getSupportedTemplateEngineName()
	 * @return string this class name {@link FileSystemTemplateEngine}
	 * @since Version 5.0
	 * @internal
	 */
	public function getSupportedTemplateEngineName(){
		return 'FileSystemTemplateEngine';
	}

	/**
	 * Set content filename.
	 *
	 * Set filename to send with content-disposition header.
	 *
	 * @param string $filename
	 * @see http://www.ietf.org/rfc/rfc2183.txt
	 * @see PageFactoryFileSystemTemplate::setContentDisposition()
	 * @return void
	 */
	public function setContentFilename($filename){
		assert('is_string($filename)');
		$this->content_filename = $filename;
	}

	/**
	 * Set Content disposition.
	 *
	 * Set the value of the content-disposition HTTP header.
	 *
	 * @see http://www.ietf.org/rfc/rfc2183.txt
	 * @param string $disposition
	 * @return void
	 */
	public function setContentDisposition($disposition='inline'){
		assert('is_string($disposition)');
		$this->content_disposition = $disposition;
	}

	/**
	 * Cleanup template before sending output.
	 *
	 * Send the correct http headers for files served by the filesystem.
	 *
	 * @see PageFactoryTemplate::cleanup()
	 * @uses PageFactoryFileSystemTemplate::$content_disposition
	 * @uses PageFactoryFileSystemTemplate::$content_filename
	 * @uses PageFactoryWebAbstractTemplate::cleanup()
	 * @return void
	 * @internal
	 */
	public function cleanup(){
		if(!is_null($this->content_disposition)){
			if(!is_null($this->content_filename)){
				header('Content-Disposition: '.$this->content_disposition.'; filename="'.$this->content_filename.'"');
			} else {
				header('Content-Disposition: '.$this->content_disposition);
			}
		}
		parent::cleanup();
	}
}


//*****************************************************************//
//******************* File system file class **********************//
//*****************************************************************//
/**
 * File System File class.
 *
 * The File System file class is a file representation of
 * a file stored within corelib's file system. A file is
 * indentified by a unique UUID. When adding a new file to
 * the file system the method {@link FileSystemFile::create()}
 * should be used.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @package Base
 * @subpackage FileSystem
 * @category corelib
 */
class FileSystemFile extends File {


	//*****************************************************************//
	//**************** FileSystemFile Class Properties ****************//
	//*****************************************************************//
	/**
	 * @var string Filesystem file id
	 * @internal
	 */
	private $id;
	/**
	 * @var string Original file name (eg. somefile.txt)
	 * @internal
	 */
	private $filename;


	//*****************************************************************//
	//****************** FileSystemFile Class Methods *****************//
	//*****************************************************************//
	/**
	 * Create new file instance.
	 *
	 * @param string $id file UUID
	 * @throws BaseException
	 * @return void
	 */
	public function __construct($id = null){
		if(is_null($id)){
			throw new BaseException('Cannot create empty instance of FileSystemFile, use FileSystemFile::create() instead.');
		} else {
			assert('is_string($id)');
			$this->id = $id;
		}
	}

	/**
	 * Read file data from filesystem.
	 *
	 * @uses FileSystemFile::_mkdirString()
	 * @uses FileSystemFile::_readFileData()
	 * @uses FileSystemFile::$id
	 * @uses File::__construct()
	 * @return boolean true if file exists else return false
	 */
	public function read(){
		$file = $this->_mkdirString($this->id).$this->id;
		if(is_file($file)){
			parent::__construct($file);
			$this->_readFileData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return file UUID.
	 *
	 * @return string file UUID
	 * @uses FileSystemFile::$id
	 */
	public function getID(){
		return $this->id;
	}

	/**
	 * Create a new file.
	 *
	 * Create a new file within corelib's filesystem
	 * based on a instance of the {@link File} class.
	 *
	 * This action will copy the original file and leave
	 * it for the user to decide what to do with it.
	 *
	 * @uses RFC4122::generate()
	 * @uses FileSystemFile::_create()
	 * @param File $file file object instance
	 * @return FileSystemFile file instance
	 */
	public static function create(File $file){
		$f = new FileSystemFile();
		$f->_create(RFC4122::generate(), $file);
		return $f;
	}

	/**
	 * Get original filename.
	 *
	 * This method returns the original filename,
	 * as the file name was, when the file was added
	 * to corelib's filesystem (eg. somefile.txt)
	 *
	 * @see File::getFilename()
	 * @uses FileSystemFile::$filename
	 * @return string Original file name
	 */
	public function getFilename(){
		return $this->filename;
	}

	/**
	 * Create file in filesystem.
	 *
	 * @uses FileSystemFile::_writeFileData()
	 * @uses FileSystemFile::_mkdir()
	 * @uses FileSystemFile::$filename
	 * @uses File::getRealname()
	 * @uses File::getFilename()
	 * @uses File::cp()
	 * @uses File::__construct()
	 * @param string $id File UUID
	 * @param File $file File to create in filesystem.
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _create($id, File $file){
		$this->id = $id;
		$this->filename = $file->getFilename();

		$file->cp($this->_mkdir($id).$id);
		parent::__construct($file->cp($this->_mkdir($id).$id)->getRealname());
		$this->_writeFileData();
		return true;
	}

	/**
	 * Write file data to support file.
	 *
	 * Write a php file containing a array with
	 * file informations for a file.
	 *
	 * @uses File::getRealname()
	 * @uses FileSystemFile::getFilename()
	 * @return boolean true in success, else return false
	 * @internal
	 */
	private function _writeFileData(){
		if(file_put_contents($this->getRealname().'.data', '<?php $this->filename = \''.addcslashes($this->getFilename(), '\'').'\'; ?>')){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Read file data from support file.
	 *
	 * @uses File::getRealname()
	 * @return void
	 * @internal
	 */
	private function _readFileData(){
		include($this->getRealname().'.data');
	}

	/**
	 * Create directory structure.
	 *
	 * Create a directory structure based on file id
	 * in order to store the file in the right location.
	 *
	 * @uses FileSystemFile::_mkdirString()
	 * @param string $id File UUID
	 * @return string path where to put the file.
	 * @internal
	 */
	private function _mkdir($id){
		$id = $this->_mkdirString($id);
		if(!is_dir($id)){
			mkdir($id, 0777, true);
		}
		return $id;
	}

	/**
	 * Generate directory structure string.
	 *
	 * @uses FILE_SYSTEM_FILE_DIR
	 * @param string $id File UUID
	 * @return return string path
	 * @internal
	 */
	private function _mkdirString($id){
		$id = str_replace('-', '', $id);
		$id = implode('/', str_split($id, 3));
		return FILE_SYSTEM_FILE_DIR.$id.'/';
	}
}
?>