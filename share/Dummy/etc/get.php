<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib dummy website get file
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
 * @package Dummy
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 * @filesource
 */

// Generic Pages
$pages['/'] = 'lib/http/get/index.php';

$pages['/404/'] = array('page'=>'lib/http/get/errors.php',
						'exec'=>'error404');

$pages['/500/'] = array('page'=>'lib/http/get/errors.php',
						'exec'=>'error500');


/*
EXAMPLES ON USING CUSTOM METHOD OVERRIDES
$pages['/'] = 'lib/http/get/corelib/about.php';

// Static
$pages['/manager/ajax/page/create/'] = array('page'=>'lib/ajax/get/manager/page.php',
                                             'exec'=>'create');

// Dynamix regex
$rpages[] = array('type'=>'regex',
                  'expr'=>'/^\/manager\/(test)\/([0-9]+)\/$/',
                  'exec'=>'\\1(\\2)',
                  'page'=>'lib/http/get/test.php');

// Dynamic Meta format
$rpages[] = array('type'=>'PageFactoryMetaPageResolver',
                  'expr'=>'/manager/(function)/(int:id)/(string:somestring)/',
                  'exec'=>'id, somestring',
                  'page'=>'lib/http/get/test.php');

// Dynamic Meta format with a static function
$rpages[] = array('type'=>'PageFactoryMetaPageResolver',
                  'expr'=>'/manager/(int:id)/(string:somestring)/',
                  'exec'=>'function: id, somestring',
                  'page'=>'lib/http/get/test.php');
*/
?>