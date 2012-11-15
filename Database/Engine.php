<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database connection interface.
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
 * @author Steffen Sørensen <steffen@sublife.dk>
 * @copyright Copyright (c) 2012 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version ($Id$)
 * @see Corelib\Base\Database\Connection
 */
namespace Corelib\Base\Database;

/**
 * Database Engine interface.
 *
 * This defines how a DatabaseEngine is implemented
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 * @api
 */
interface Engine {


	//*****************************************************************//
	//****************** Database interface methods *******************//
	//*****************************************************************//
	/**
	 * Execute query.
	 *
	 * @param Query $query
	 */
	public function query(Query $query);
	/**
	 * Get DAO class prefix.
	 *
	 * @return string
	 */
	public function getPrefix();
	/**
	 * Start transaction.
	 */
	public function startTransaction();
	/**
	 * Commit transaction.
	 */
	public function commit();
	/**
	 * Rollback transaction.
	 */
	public function rollback();
	/**
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string.
	 */
	public function escapeString($string);
	/**
	 * Analyse Query.
	 *
	 * Analyses a query and return a array with the results
	 * The return array is a multi dimensional array
	 *
	 * @param Query $query
	 * @todo add output array example
	 * @return array
	 */
	public function analyse(Query $query);
}