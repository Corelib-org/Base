<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * HTTP Error message handler.
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
 * @package Dummy
 * @subpackage Controllers-Get
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Sørensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @ignore
 */

/**
 * Default get http error handling controller
 *
 * @package Dummy
 * @subpackage Controllers-Get
 * @ignore
 */
class WebPage extends DummyPageGet {

	/**
	 * Build index page.
	 *
	 * @return void
	 */
	public function error404(){
		$this->xsl->addTemplate('pages/errors/404.xsl');
	}

	/**
	 * Build index page.
	 *
	 * @return void
	 */
	public function error500(){
		$this->xsl->addTemplate('pages/errors/500.xsl');
	}
}
?>