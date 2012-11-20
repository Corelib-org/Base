<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page factory redirect page resolver.
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
 * @version 1.0.0 ($Id: WebAbstractTemplate.php 5176 2010-03-04 12:33:06Z wayland $)
 */

//*****************************************************************//
//************** PageFactoryResolverRedirect class ****************//
//*****************************************************************//
/**
 * Page factory resolver redirect.
 *
 * This page resolver can be used to setup redirect
 * directly in the get.php or post.php.
 *
 * this resolver is registered by default.
 *
 * to do redirect a page entry must be defined like the following example:
 *
 * $pages[] = array('type'=>'redirect',
 *                  'expr'=>'/^\/somelocation(\/.*)$/',
 *                  'exec'=>'http://www.corelib.org\\1');
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
// class PageFactoryResolverRedirect implements PageFactoryPageResolver {


	//*****************************************************************//
	//********** PageFactoryResolverRedirect class methods ************//
	//*****************************************************************//
	/**
	 * Resolve page expression.
	 *
	 * Check and see if expression matches against url, if it does
	 * sent the Location header and exit the script.
	 *
	 * @see PageFactoryPageResolver::resolve()
	 * @param string $expr expression read from page lookup table
	 * @param string $exec execution statement read from page lookup table
	 * @param string $url request url
	 * @return boolean true on success, else return false
	 * @internal
	 * @todo make a better implimentation in order for better support in PageFactory
	 */
/*	public function resolve($expr, $exec, $url){
		if(preg_match($expr, $url)){
			header('Location: '.preg_replace($expr, $exec, $url));
			exit;
		}
		return true;
	}
*/
	/**
	 * Get expression.
	 *
	 * @see PageFactoryPageResolver::getExpression()
	 * @return boolean false
	 */
/*	public function getExpression(){
		return false;
	}
*/
	/**
	 * Get execution statement.
	 *
	 * @see PageFactoryPageResolver::getExecute()
	 */
/*	public function getExecute(){
		return false;
	}
}*/
?>