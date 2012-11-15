<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database data access base object.
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
 * Database Data access object.
 *
 * The DataAccessObject replaces the old DatabaseDAO object.
 *
 * @todo write documentation and usage example
 * @todo Impliment method calls for Corelib\Base\Database\Connection proxying
 * @see Corelib\Base\Database\Connection
 * @api
 */
class DataAccessObject {

	/**
	 * Connection instance reference.
	 *
	 * @var Connection
	 */
	protected $database = null;

	/**
	 * Create new instance.
	 *
	 * @param Connection $database
	 * @internal
	 */
	public function __construct(Connection $database){
		$this->database = $database;
	}

	/**
	 * Run abitrary method on the Connection class.
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return mixed
	 * @internal
	 */
	public function __call($method, $args){
		return call_user_func_array(array($this->database, $method), $args);
	}

	/**
	 * Execute a query.
	 *
	 * @param Query $query
	 * @see Connection::query()
	 * @uses DataAccessObject::$database
	 */
	protected function query(Query $query){
		return $this->database->query($query, get_class($this));
	}

	/**
	 * Execute a query using the master connection.
	 *
	 * @param Query $query
	 * @see Connection::masterQuery()
	 * @uses DataAccessObject::$database
	 */
	protected function masterQuery(Query $query){
		return $this->database->masterQuery($query, get_class($this));
	}

	/**
	 * Execute a query using the slave connection.
	 *
	 * @param Query $query
	 * @see Connection::slaveQuery()
	 * @uses DataAccessObject::$database
	 */
	protected function slaveQuery(Query $query){
		return $this->database->slaveQuery($query, get_class($this));
	}

}