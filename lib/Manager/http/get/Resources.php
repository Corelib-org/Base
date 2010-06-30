<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager resource get controller.
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Interfaces.php 5218 2010-03-16 13:07:41Z wayland $)
 * @internal
 */

/**
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @ignore
 */
class WebPage extends PageBase {

	public function getResource($handler, $resource){
		$manager = Manager::getInstance();
		$resource = $manager->getResource($handler, $resource);
		$extension = substr($resource, -3);

		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', filemtime($resource)));
 		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
		header('Cache-Control: public, max-age=86400');
		header('Pragma:');

		switch ($extension){
			case 'css':
				header('Content-Type: text/css; charset=utf-8');
				echo file_get_contents($resource);
				break;
			case 'jpg':
				header('Content-Type: image/jpeg');
				echo file_get_contents($resource);
				break;
			case 'epg':
				header('Content-Type: image/jpeg');
				echo file_get_contents($resource);
				break;
			case 'gif':
				header('Content-Type: image/gif');
				echo file_get_contents($resource);
				break;
			case 'png':
				header('Content-Type: image/png');
				echo file_get_contents($resource);
				break;
			case '.js':
				header('Content-Type: text/javascript; charset=utf-8');
				echo file_get_contents($resource);
				break;
			default:
				trigger_error('Illegal Resource type!', E_USER_ERROR);
		}
		exit;
	}
}
?>