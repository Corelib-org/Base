<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib File upload class.
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
//********************** File upload class ************************//
//*****************************************************************//
/**
 * File upload class.
 *
 * @category corelib
 * @package Base
 * @subpackage FileSystem
 */
class FileUpload extends File {


	//*****************************************************************//
	//**************** File upload class properties *******************//
	//*****************************************************************//
	/**
	 * HTTP Upload error code.
	 *
	 * @var integer
	 * @internal
	 */
	private $error = null;

	/**
	 * Original Filename.
	 *
	 * @var string
	 * @internal
	 */
	private $original_filename = null;


	//*****************************************************************//
	//***************** File upload class methods *********************//
	//*****************************************************************//
	/**
	 * Create new file upload instance.
	 *
	 * @param string $upload http upload name.
	 * @return void
	 */
	public function __construct($upload){
		try {
			if(isset($_FILES[$upload])){
				$this->error = $_FILES[$upload]['error'];
				$this->original_filename = $_FILES[$upload]['name'];
				$this->_setMimeType($_FILES[$upload]['type']);
				if($_FILES[$upload]['error'] == UPLOAD_ERR_OK){
					parent::__construct($_FILES[$upload]['tmp_name']);
				}
			} else {
				throw new BaseException('File upload identifier not found: '.$upload, E_USER_WARNING);
			}
		} catch (BaseException $e){
			echo $e;
		}
	}

	/**
	 * Get HTTP upload error code
	 *
	 * @return integer error code {@link http://www.php.net/manual/en/features.file-upload.errors.php}
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * Get original filename.
	 *
	 * @return string
	 */
	public function getName(){
		return $this->original_filename;
	}

	/**
	 * Rename file.
	 *
	 * @param string $target filename.
	 * @return mixed File instance on success, else return false.
	 */
	public function mv($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getRealname()){
				throw new BaseException('Unable to copy file to it self');
			} else {
				if(move_uploaded_file($this->getRealname(), $target)){
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
}
?>