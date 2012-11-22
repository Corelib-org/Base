<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database MySQLi engine.
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
namespace Corelib\Base\Database\MySQLi;
use Corelib\Base\Database\Query as ConnectionQuery;

/**
 * MySQLi Query.
 */
class Query extends ConnectionQuery {

	//*****************************************************************//
	//**************** MySQLiQuery class properties *******************//
	//*****************************************************************//
	/**
	 * mysqli_result instance.
	 *
	 * @var mysqli_result
	 * @internal
	 */
	protected $result = null;
	/**
	 * @var string query error
	 * @internal
	 */
	protected $error = null;
	/**
	 * @var integer query error number
	 * @internal
	 */
	protected $errno = null;
	/**
	 * @var integer last insert id
	 * @internal
	 */
	protected $insertid = null;
	/**
	 * @var integer affected rows
	 * @internal
	 */
	protected $affected_rows = null;

	/**
	 * Run query and populate object.
	 *
	 * @param mixed $connection mysqli instance
	 * @uses Query::$instance
	 * @uses Query::$result
	 * @uses Query::$error
	 * @uses Query::$errno
	 * @uses Query::$insertid
	 * @uses Query::getQuery()
	 * @internal
	 */
	public function execute($connection){
		$this->result = $connection->query($this->getQuery());
		$this->error = $connection->error;
		$this->errno = $connection->errno;
		$this->insertid = $connection->insert_id;
		$this->affected_rows = $connection->affected_rows;
	}

	/**
	 * Get Query string.
	 *
	 * @uses MySQLiQuery::$query
	 * @return string query
	 */
	public function getQuery(){
		return $this->query;
	}

	/**
	 * Get error description.
	 *
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$query
	 * @return string error description
	 */
	public function getError(){
		return $this->error.' (#'.$this->errno.')'."\n<br/><br/>".$this->query;
	}
	/**
	 * Get error code.
	 *
	 * @uses MySQLiQuery::$errno
	 * @return integer mysql error code
	 */
	public function getErrno(){
		return $this->errno;
	}
	/**
	 * Get get row count in query.
	 *
	 * @uses MySQLiQuery::$result
	 */
	public function getNumRows(){
		return $this->result->num_rows;
	}

	/**
	 * Get insert ID.
	 *
	 * @uses MySQLiQuery::$insertid
	 * @return integer last insert id
	 */
	public function getInsertID(){
		return $this->insertid;
	}

	/**
	 * Fetch row as array
	 *
	 * @uses MySQLiQuery::$result
	 * @return mixed[]
	 */
	public function fetchArray(){
		return $this->result->fetch_array();
	}

	/**
	 * Fetch row as array
	 *
	 * @uses Query::$result
	 * @return mixed[]
	 */
	public function fetchRow(){
		return $this->result->fetch_row();
	}

	/**
	 * Fetch row as an associative array
	 *
	 * @uses MySQLiQuery::$result
	 * @return mixed[]
	 */
	public function fetchAssoc(){
		return $this->result->fetch_assoc();
	}

	/**
	 * Fetch fields as array.
	 *
	 * @uses Query::$result
	 * @return mixed[]|boolean if succesfull return array else return false
	 */
	public function fetchFields(){
		if($this->result){
			return $this->result->fetch_fields();
		} else {
			return false;
		}
	}
	/**
	 * Get affected rows.
	 *
	 * @uses MySQLiQuery::$instance
	 * @return integer
	 */
	public function getAffectedRows(){
		return $this->affected_rows;
	}

	/**
	 * Adjusts the result pointer to an arbitary row in the result.
	 *
	 * @uses Query::$result
	 * @param integer $offset The field offset. Must be between zero and the total number of rows minus one
	 * @return mixed[]
	 */
	public function dataSeek($offset){
		return $this->result->data_seek($offset);
	}

	/**
	 * Convert class to string.
	 *
	 * When the class is converted to a string, the query is returned
	 *
	 * @see Query::getQuery()
	 * @return string query
	 * @internal
	 */
	public function __toString(){
		return $this->getQuery();
	}
}
?>