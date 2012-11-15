<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database query.
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
 * Database Query abstract class.
 *
 * This defines how a Query is implemented
 */
abstract class Query {


	//*****************************************************************//
	//*************** Database Query class properties *****************//
	//*****************************************************************//
	/**
	 * Query
	 *
	 * @var mixed query
	 * @internal
	 */
	protected $query = null;


	//*****************************************************************//
	//***************** Database Query class methods ******************//
	//*****************************************************************//
	/**
	 * Create new query object instance.
	 *
	 * @param string $query
	 */
	public function __construct($query) {
		$this->query = $query;
	}
	/**
	 * Execute query.
	 *
	 * @param mixed $connection
	 * @return true on success, else return false
	 */
	abstract public function execute($connection);
	/**
	 * Get query.
	 *
	 * @return string query
	 */
	abstract public function getQuery();
	/**
	 * Get error description.
	 *
	 * @return string error description
	 */
	abstract public function getError();

	/**
	 * Get error code.
	 *
	 * @return integer error code, else return false or 0
	 */
	abstract public function getErrno();

	/**
	 * Get number of rows of select query.
	 *
	 * @return integer
	 */
	abstract public function getNumRows();
	/**
	 * Get last insert ID.
	 *
	 * @return integer row id, else return false
	 */
	abstract public function getInsertID();
	/**
	 * Fetch row as array from result.
	 *
	 * @return array row
	 */
	abstract public function fetchArray();
	/**
	 * Fetch row from result.
	 *
	 * @return array row
	 */
	abstract public function fetchRow();
	/**
	 * Fetch row as associative array from result.
	 *
	 * @return array row
	 */
	abstract public function fetchAssoc();
	/**
	 * Fetch fields from result.
	 *
	 * @return array fields
	 */
	abstract public function fetchFields();
	/**
	 * Get affected rows.
	 *
	 * return integer affected rows
	 */
	abstract public function getAffectedRows();
	/**
	 * Adjusts the result pointer to an arbitary row in the result.
	 *
	 * @param integer $offset The field offset. Must be between zero and the total number of rows minus one
	 * @return boolean true on success, else return false
	 */
	abstract public function dataSeek($offset);
}
?>