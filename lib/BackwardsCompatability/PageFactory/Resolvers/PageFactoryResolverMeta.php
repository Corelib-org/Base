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
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//************** PageFactoryResolverRedirect class ****************//
//*****************************************************************//
/**
 * Page factory meta resolver.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
/*
class PageFactoryResolverMeta implements PageFactoryPageResolver {
	private $exec = null;
	private $expr = null;

	public function resolve($expr, $exec, $url){
		preg_match_all('/\((.*?)\)/', $expr, $result);

		$param = array();
		while(list($key, $val) = each($result[1])){
			if(strstr($val, ':')){
				list($type, $name) = explode(':', $val);
			} else {
				$type = 'function';
				$name = $val;
			}
			switch ($type) {
				case 'int':
					$expr = str_replace($val, '[0-9]+', $expr);
					$param[] = '(int) \\'.($key + 1).'';
					break;
				case 'function':
					$expr = str_replace($val, '[a-z]+', $expr);
					$function = '\\'.($key + 1);
					break;
				case 'string':
					$expr = str_replace($val, '[a-z]+', $expr);
					$param[] = '(string) \'\\'.($key + 1).'\'';
					break;
			}
		}
		if(strstr($exec, ':')){
			list($function) = explode(':', $exec, 2);
		}
		$expr = '/^'.str_replace('/', '\/', $expr).'$/';
		$exec = $function.'('.implode(', ', $param).')';
		$this->exec = $exec;
		$this->expr = $expr;
		return true;
	}

	public function getExpression(){
		return $this->expr;
	}
	public function getExecute(){
		return $this->exec;
	}
}
*/
?>