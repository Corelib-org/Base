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

/**
 * MySQLi Query Statement.
 *
 * @api
 */
class Statement extends Query {


	//*****************************************************************//
	//************ MySQLiQueryStatement class properties **************//
	//*****************************************************************//
	/**
	 * mysqli_stmt instance.
	 *
	 * @var mysqli_stmt Mysqli stmt instance
	 * @internal
	 */
	private $statement = null;

	/**
	 * List of values to bind to statement.
	 *
	 * @var array query values
	 * @interal
	 */
	private $bind = array();

	/**
	 * List of blob values to bind to the statement.
	 *
	 * @var array query values larger than 256 bytes
	 * @internal
	 */
	private $blob = array();

	/**
	 * List of previously prepared statement.
	 *
	 * @var array
	 * @internal
	 */
	private static $statements = array();

	//*****************************************************************//
	//************** MySQLiQueryStatement class methods ***************//
	//*****************************************************************//
	/**
	 * Construct object and optionally pass data.
	 *
	 * @uses Query::__construct()
	 * @uses Statement::bind()
	 * @param string $query mysql query
	 * @param mixed $item,... values to pass to the statement
	 */
	public function __construct($query, $item=null /*, [$items...] */){
		parent::__construct($query);

		$bind = func_get_args();
		if(sizeof($bind) > 0){
			array_shift($bind);
			call_user_func_array(array($this, 'bind'), $bind);
		}
	}

	/**
	 * Bind values to statement.
	 *
	 * @uses MySQLiQueryStatement::_bindValue()
	 * @uses MySQLiQueryStatement::$bind
	 * @uses MySQLiQueryStatement::$blob
	 * @param mixed $item,... values to pass to the statement
	 */
	public function bind($item=null /*, [$items...] */){
		$this->bind = array();
		$this->blob = array();
		$bind = func_get_args();

		foreach ($bind as $val) {
			if(is_array($val)){
				foreach ($val as $subval){
					$this->_bindValue($subval);
				}
			} else {
				$this->_bindValue($val);
			}
		}
	}

	/**
	 * Prepare a value for binding.
	 *
	 * @uses MySQLiQueryStatement::$bind
	 * @uses MySQLiQueryStatement::$blob
	 * @uses MySQLiTools::parseBooleanValue()
	 * @param mixed $val Value to bind
	 * @internal
	 */
	private function _bindValue($val){
		if(isset($this->bind['param'])){
			$key = sizeof($this->bind['param']);
		} else {
			$key = 0;
		}
		$this->bind['param'][$key] = $val;
		if(is_string($val) && strlen($val) < 256){
			$this->bind['types'][$key] = 's';
		} else if(is_string($val)){
			$this->bind['types'][$key] = 'b';
			$this->blob[$key] = $val;
			$this->bind['param'][$key] = '';
		} else if(is_integer($val)){
			$this->bind['types'][$key] = 'i';
		} else if(is_float($val)){
			$this->bind['types'][$key] = 'd';
		} else if(is_bool($val)){
			$this->bind['param'][$key] = MySQLiTools::parseBooleanValue($val, false);
			$this->bind['types'][$key] = 's';
		} else {
			$this->bind['types'][$key] = 's';
		}
	}

	/**
	 * Execute query.
	 *
	 * @param mixed $connection
	 * @uses Query::$instance
	 * @uses Statement::$statement
	 * @uses Query::$error
	 * @uses Query::$errno
	 * @uses Query::$insertid
	 * @uses Statement::$bind
	 * @uses Statement::$blob
	 * @uses Query::getQuery()
	 * @return true on success, else return false
	 * @internal
	 */
	public function execute($connection){
		if(is_null($this->statement)){
			$query = $this->getQuery();
			$md5 = md5($query);
			if(isset(self::$statements[$md5])){
				$this->statement = self::$statements[$md5];
			} else {
				if(!$this->statement = $connection->prepare($query)){
					$this->error = $connection->error;
					$this->errno = $connection->errno;
					return false;
				}
				self::$statements[$md5] = $this->statement;
			}
		}

		// If no data have been bound to query don't try to bind anything.
		if(sizeof($this->bind) > 0){
			$types = implode('', $this->bind['types']);

			$params = array(&$types);
			foreach ($this->bind['param'] as $key => $param){
				$params[] = &$this->bind['param'][$key];
			}


			call_user_func_array(array($this->statement, 'bind_param'), $params);

			foreach ($this->blob as $key => $val){
				$this->statement->send_long_data($key, $val);
			}
		}
		$this->statement->execute();
		$this->result = $this->statement->get_result();
		$this->error = $this->statement->error;
		$this->errno = $this->statement->errno;
		$this->insertid = $this->statement->insert_id;
		return true;
	}

	/**
	 * Get number of rows in query.
	 *
	 * @uses MySQLiQueryStatement::$statement
	 */
	public function getNumRows(){
		return $this->statement->num_rows;
	}
	/**
	 * Get affected rows.
	 *
	 * @uses MySQLiQueryStatement::$statement
	 * @return integer
	 */
	public function getAffectedRows(){
		return $this->statement->affected_rows;
	}

	/**
	 * Get error description.
	 *
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$query
	 * @return string error description
	 */
	public function getError(){
		$values = array();
		if(isset($this->bind['param']) && is_array($this->bind['param'])){
			foreach ($this->bind['param'] as $key => $param){
				$values[] = $param;
			}
		}

		return $this->error.' (#'.$this->errno.')'."\n<br/><br/>".$this->query.' Values: '.implode(', ', $values);
	}

	/**
	 * Destroy object.
	 *
	 * @uses MySQLiQueryStatement::$statement
	 * @internal
	 */
	public function __destruct(){
		@$this->statement->free_result();
	}
}