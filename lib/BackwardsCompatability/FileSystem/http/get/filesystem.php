<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib HTTP Get request handler.
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
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Sørensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @since Version 5.0
 * @category corelib
 * @package Base
 * @subpackage FileSystem
 * @internal
 */


//*****************************************************************//
//********** File system HTTP GET request handler class ***********//
//*****************************************************************//
/**
 * File system HTTP GET request handler.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @package corelib
 * @subpackage Base
 * @ignore
 */
class WebPage extends PageBase {

	//*****************************************************************//
	//****** File system HTTP GET request handler Class Methods *******//
	//*****************************************************************//
	/**
	 * Dummy required build function.
	 *
	 * @see PageBase::build()
	 * @internal
	 */
	public function build(){ }

	/**
	 * Get file from filesystem.
	 *
	 * @uses PageFactoryFileSystemTemplate
	 * @uses PageFactoryWebAbstractTemplate::setContentType()
	 * @uses PageFactoryWebAbstractTemplate::setExpire()
	 * @uses PageFactoryWebAbstractTemplate::setLastModified()
	 * @uses PageFactoryFileSystemTemplate::setContentFilename()
	 * @uses PageFactoryFileSystemTemplate::cleanup()
	 * @uses FileSystemFile
	 * @uses File::fread()
	 * @param string $file File UUID
	 * @return void
	 * @internal
	 */
	public function getFile($file){
		$template = new PageFactoryFileSystemTemplate();
		$file = new FileSystemFile($file);

		if($file->read()){
			$template->setContentType($file->getMimeType());
			$template->setExpire($file->getModificationTime()+86400);
			$template->setLastModified($file->getModificationTime());
			$template->setContentFilename($file->getFilename());
			$template->cleanup();

			while($data = $file->fread()){
				echo $data;
			}
		} else {
			$template->setContentType('text/html');
			$template->cleanup();
			echo '<html><head><title>404 File not found</title></head><body><h1>File Not Found</h1><p>The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.</p><hr><i><a href="http://www.corelib.org/">Corelib</a></i></body></html>';
		}
		exit;
	}
}

?>