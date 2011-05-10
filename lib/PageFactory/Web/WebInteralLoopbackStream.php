<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Internal loopback stream wrapper.
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
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: XMLOutput.php 5170 2010-03-03 13:07:25Z wayland $)
 * @since Version 5.0
 */

//*****************************************************************//
//****************** WebInteralLoopbackStream class ***************//
//*****************************************************************//
/**
 * Internal loopback stream wrapper.
 *
 * The internal loopback stream allows you to do loopback requests
 * into a corelib and retrieving output from a request using a relative
 * path. usage example: file_get_contents('internal://some/local/path');
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @see LoopbackStream
 * @since Version 5.0
 */
class WebInteralLoopbackStream extends LoopbackStream {


	//*****************************************************************//
	//************ WebInteralLoopbackStream class methods *************//
	//*****************************************************************//
	/**
	 * Open stream.
	 *
	 * @see LoopbackStream::stream_open()
	 * @internal
	 */
	public function stream_open($path , $mode , $options , &$opened_path){
		if(!defined('BASE_URL')){
			Base::getInstance()->loadClass('PageFactoryWebAbstractTemplate');
		}

		$url = parse_url($path);
		$path = str_replace($url['scheme'].'://', BASE_URL, $path);
		return parent::stream_open($path , $mode , $options , &$opened_path);
	}
}

stream_wrapper_register('internal', 'WebInteralLoopbackStream');
?>