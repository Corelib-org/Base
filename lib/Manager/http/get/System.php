<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager system tools get controller.
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
class WebPage extends ManagerPage {

	public function database(){
		$this->addContent(new DatabaseTool());
		$this->xsl->addJavaScript('corelib/resource/manager/javascript/database.js');
		$this->xsl->addTemplate('Base/Share/xsl/pages/system/database.xsl');
	}

	public function cache(){
		$cache = new ManagerCacheStatus();
		$cache->setCacheSizeConverter(new StringConverterFileSize(2));
		$this->addContent($cache);
		$this->xsl->addTemplate('Base/Share/xsl/pages/system/cache.xsl');
	}

	public function cacheClear(){
		$cache = new ManagerCacheStatus();
		$cache->clear();
		$this->xsl->setLocation('corelib/system/cache/');
	}
}
?>