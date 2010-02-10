<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib File class.
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
//**************************** File class *************************//
//*****************************************************************//
/**
 * File representation class.
 *
 * @category corelib
 * @package Base
 * @subpackage FileSystem
 */
class File {


	//*****************************************************************//
	//********************** File class properties ********************//
	//*****************************************************************//
	/**
	 * Absolute filename and path.
	 *
	 * @var string filename
	 * @internal
	 */
	private $file = null;

	/**
	 * File mime-type
	 *
	 * @var string mime-type
	 * @internal
	 */
	private $mime_type = null;

	/**
	 * @var resource file-pointer
	 * @internal
	 */
	private $pointer = null;


	//*****************************************************************//
	//************************ File class methods *********************//
	//*****************************************************************//
	/**
	 * Create new file instance.
	 *
	 * @param string $file file
	 * @uses File::$file
	 * @return void
	 */
	public function __construct($file){
		assert('is_string($file)');

		if(!is_file($file)){
			$this->file = realpath(dirname($file)).'/'.$file;
		} else if(!is_dir($file)){
			$this->file = realpath($file);
		} else {
			throw new BaseException('Unknow File error', E_USER_ERROR);
		}

	}

	/**
	 * Get filename.
	 *
	 * @uses File::$file
	 * @return string filename
	 */
	public function getFilename(){
		return basename($this->file);
	}

	/**
	 * Get filename and path.
	 *
	 * @return string
	 */
	public function getRealname(){
		return $this->file;
	}

	/**
	 * Get file path.
	 *
	 * @return string
	 */
	public function getPath(){
		return dirname($this->getRealname());
	}

	/**
	 * Get mime-type.
	 *
	 * @uses finfo_open()
	 * @return string mimetype
	 */
	public function getMimeType(){
		if(is_null($this->mime_type)){
			$finfo = finfo_open(FILEINFO_MIME);
			$this->mime_type = finfo_file($finfo, $this->getRealName());
			finfo_close($finfo);
		}
		return $this->mime_type;
	}

	/**
	 * Get filesize.
	 *
	 * @return float bytes
	 */
	public function getSize(){
		return filesize($this->getRealName());
	}

	/**
	 * Get modification time.
	 *
	 * @return integer unixtimestamp
	 */
	public function getModificationTime(){
		return filemtime($this->getRealName());
	}

	/**
	 * Open file pointer.
	 *
	 * @param string $mode {@link http://www.php.net/manual/en/function.fopen.php}
	 * @return true on success, else return false.
	 */
	public function fopen($mode = 'r'){
		if($this->pointer = fopen($this->getRealName(), $mode)){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Seek on file pointer.
	 *
	 * @see File::fopen()
	 * @param integer $offset
	 * @param integer $whence {@link http://www.php.net/manual/en/function.fseek.php}
	 * @return unknown_type
	 */
	public function fseek($offset, $whence=SEEK_SET){
		return fseek($this->pointer, $offset, $whence);
	}

	/**
	 * Test for end-of-file.
	 *
	 * @return boolean true if EOF reached, else return false
	 */
	public function feof(){
		return feof($this->pointer);
	}

	/**
	 * Binary-safe read.
	 *
	 * @param integer $buffer buffer length
	 * @return mixed string buffer, else return false
	 */
	public function fread($buffer=1024){
		if(is_null($this->pointer)){
			$this->fopen();
		}
		if($this->feof() !== true){
			return fread($this->pointer, $buffer);
		} else {
			return false;
		}
	}

	/**
	 * Gets line and parse for CSV fields.
	 *
	 * @param integer $buffer buffer length
	 * @param string $delimiter field delimiter (one character only)
	 * @param string $enclosure field enclosure character (one character only).
	 * @param string $escape escape character (one character only).
	 * @return mixed array on success, else return false
	 */
	public function fgetcsv($buffer=1024, $delimiter=',', $enclosure='"', $escape='\\'){
		if(is_null($this->pointer)){
			$this->fopen();
		}
		if($this->feof() !== true){
			return fgetcsv($this->pointer, $buffer, $delimiter, $enclosure);
		} else {
			return false;
		}
	}

	/**
	 * Copy file.
	 *
	 * @param string $target
	 * @return mixed new File instance on success, else return false
	 */
	public function cp($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getRealname()){
				throw new BaseException('Unable to copy file it self');
			} else {
				if(copy($this->getRealname(), $target)){
					return new File($target);
				} else {
					return false;
				}
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
	}

	/**
	 * Move file.
	 *
	 * @param string $target
	 * @return mixed file instance on success, else return false
	 */
	public function mv($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getRealName()){
				throw new BaseException('Unable to copy file it self');
			} else {
				if(rename($this->getRealName(), $target)){
					$this->filename = $target;
					return $this;
				} else {
					return false;
				}
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
	}

	/**
	 * Delete file.
	 *
	 * @return boolean true on success, else return false.
	 */
	public function rm(){
		unlink($this->getRealName());
		if(!is_file($this->getRealName())){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Resolve target path name.
	 *
	 * @param string $target relative filename.
	 * @return string absolute filename.
	 * @internal
	 */
	protected function _resolveTarget($target){
		if(is_dir($target)){
			$target = realpath($target).'/'.$this->getName();
		} else {
			$dir = dirname($target);
			try {
				if(is_dir($dir)){
					$target = realpath($dir).'/'.basename($target);
				} else {
					throw new BaseException('Unable to resolve path, no such file or directory', E_USER_ERROR);
				}
			} catch (BaseException $e){
				echo $e;
				return false;
			}
		}
		return $target;
	}

	/**
	 * Set mime-type
	 *
	 * @param string $type
	 * @return boolean true
	 */
	protected function _setMimeType($type){
		$this->mime_type = $type;
		return true;
	}
}
?>