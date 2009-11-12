<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database MySQLi Connector.
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
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Database
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 * @filesource
 * @todo finish documentation
 */


define('DATABASE_ORDER_DESC', 'DESC');
define('DATABASE_ORDER_ASC', 'ASC');
define('DATABASE_GT', '>');
define('DATABASE_LT', '<');
define('DATABASE_EQUAL', '=');
define('DATABASE_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

/**
 * MySQLi Database engine.
 *
 * The Database class provides all basic functionality,
 * for communicating with a mysql database using PHP's
 * mysqli extension.
 *
 * @package corelib
 * @subpackage Database
 */
class MySQLiEngine implements DatabaseEngine {
	/**
	 * @var mysqli database connection
	 */
	private $connection = null;
	/**
	 * @var string hostname
	 */
	private $hostname = null;
	/**
	 * @var string username
	 */
	private $username = null;
	/**
	 * @var string password
	 */
	private $password = null;
	/**
	 * @var string database
	 */
	private $database = null;
	/**
	 * @var string connection encoding
	 */
	private $charset = 'utf8';
	/**
	 * @var boolean reconnect if connection is lost
	 */
	private $reconnect = false;

	/**
	 * MySQLi engine DAO class prefix.
	 */
	const PREFIX = 'MySQLi';

	/**
	 * Create new connection.
	 *
	 * @uses  MySQLiEngine::$hostname
	 * @uses  MySQLiEngine::$username
	 * @uses  MySQLiEngine::$password
	 * @uses  MySQLiEngine::$database
	 * @uses  MySQLiEngine::$reconnect
	 * @uses  MySQLiEngine::$charset
	 * @uses  MySQLiEngine::$pid
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param boolean $reconnect false if no reconnect should be done, else true
	 * @param string $charset connection encoding
	 */
	public function __construct($hostname, $username, $password, $database, $reconnect=false, $charset='utf8'){
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->reconnect = $reconnect;
		$this->charset = $charset;
	}

	/**
	 * Execute query.
	 *
	 * @uses BaseException
	 * @uses MySQLiEngine::$connection
	 * @uses MySQLiEngine::_connect()
	 * @uses MySQLiEngine::$reconnect
	 * @uses MySQLiQuery::setInstance()
	 * @uses MySQLiQuery::execute()
	 * @uses MySQLiQuery::getErrno()
	 * @param Query {@link MySQLiQuery mysqli query object}
	 * @return true on success, else return false
	 */
	public function query(Query $query){
		if(!$query instanceof MySQLiQuery){
			throw new BaseException('Invalid Query Object, object must be instance of MySQLiQuery');
		}
		if(is_null($this->connection)){
			$this->_connect();
		}
		$query->setInstance($this->connection);
		if($this->reconnect){
			while(true){
				$query->execute();
				if($query->getErrno() >= 2000){
					sleep(1);
					if($this->_connect()){
						$query->setInstance($this->connection);
					}
				} else {
					return true;
				}
			}
		} else {
			return $query->execute();
		}
		return false;
	}

	/**
	 * Get dao class prefix.
	 *
	 * @see DatabaseEngine::getPrefix()
	 * @return string
	 */
	public function getPrefix(){
		return self::PREFIX;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::startTransaction()
	 */
	public function startTransaction(){
		$this->query(new MySQLiQuery('START TRANSACTION'));
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::commit()
	 */
	public function commit(){
		$this->query(new MySQLiQuery('COMMIT'));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::rollback()
	 */
	public function rollback(){
		$this->query(new MySQLiQuery('ROLLBACK'));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see DatabaseEngine::analyse()
	 * @uses MySQLiQuery
	 * @uses MySQLiQuery::getQuery()
	 * @uses MySQLiEngine::query()
	 * @param Query $query {@link MySQLiQuery mysqli query object}
	 * @return array
	 */
	public function analyse(Query $query){
		$query = new MySQLiQuery('EXPLAIN '.$query->getQuery());
		$this->query($query);


		if($fieldsObj = $query->fetchFields()){
			foreach ($fieldsObj as $field){
				$fields[] = $field->name;
			}

			$i = 0;
			while($out = $query->fetchArray()){
				foreach ($fields as $field){
					$rows[$i][] = $out[$field];
				}
				$i++;
			}
			return array('columns'=>$fields, 'rows'=>$rows);
		} else {
			return false;
		}
	}

	/**
	 * Connect to database.
	 *
	 * @uses MySQLiEngine::$hostname
	 * @uses MySQLiEngine::$username
	 * @uses MySQLiEngine::$password
	 * @uses MySQLiEngine::$database
	 * @uses MySQLiEngine::$connection
	 * @uses MySQLiEngine::$charset
	 * @return true on success, else return false
	 */
	private function _connect(){
		$this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
		if($this->connection->errno === 0){
			$this->connection->query('SET NAMES '.$this->charset);
			$this->connection->query('SET CHARACTER SET '.$this->charset);
			return true;
		} else {
			return false;
		}
	}
}

/**
 * mysqli query.
 *
 * @package corelib
 * @subpackage Database
 */
class MySQLiQuery extends Query {
	/**
	 * @var mysqli
	 */
	protected $instance = null;
	/**
	 * @var mysqli_result
	 */
	protected $result = null;
	/**
	 * @var string query error
	 */
	protected $error = null;
	/**
	 * @var integer query error number
	 */
	protected $errno = null;
	/**
	 * @var integer last insert id
	 */
	protected $insertid = null;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($query){
		parent::__construct($query);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$instance
	 * @uses MySQLiQuery::$result
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$errno
	 * @uses MySQLiQuery::$insertid
	 * @uses MySQLiQuery::getQuery()
	 */
	public function execute(){
		$this->result = $this->instance->query($this->getQuery());
		$this->error = $this->instance->error;
		$this->errno = $this->instance->errno;
		$this->insertid = $this->instance->insert_id;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$instance
	 */
	public function setInstance($instance){
		$this->instance = $instance;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$query
	 */
	public function getQuery(){
		return $this->query;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$query
	 * @return string error
	 */
	public function getError(){
		return $this->error."\n<br/><br/>".$this->query;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$errno
	 * @return integer mysql error code
	 */
	public function getErrno(){
		return $this->errno;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$result
	 */
	public function getNumRows(){
		return $this->result->num_rows;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$insertid
	 * @return integer last insert id
	 */
	public function getInsertID(){
		return $this->insertid;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$result
	 * @return array
	 */
	public function fetchArray(){
		return $this->result->fetch_array();
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$result
	 * @return array|boolean if succesfull return array else return false
	 */
	public function fetchFields(){
		if($this->result){
			return $this->result->fetch_fields();
		} else {
			return false;
		}
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$instance
	 * @return integer
	 */
	public function getAffectedRows(){
		return $this->instance->affected_rows;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$result
	 * @return array
	 */
	public function dataSeek($offset){
		return $this->result->data_seek($offset);
	}

	/**
	 * @see MySQLiQuery::getQuery()
	 * @return string query
	 */
	public function __toString(){
		return $this->getQuery();
	}
}

/**
 * mysqli query statement.
 *
 * mysqli query object used for prepared statements
 *
 * @package corelib
 * @subpackage Database
 */
class MySQLiQueryStatement extends MySQLiQuery {
	/**
	 * @var mysqli_stmt
	 */
	private $statement = null;
	/**
	 * @var array query values
	 */
	private $bind = array();
	/**
	 * @var array query values larger than 256 bytes
	 */
	private $blob = array();

	/**
	 * Construct object and optionally pass data.
	 *
	 * @uses MySQLiQuery::__construct()
	 * @uses MySQLiQueryStatement::bind()
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
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQuery::$instance
	 * @uses MySQLiQueryStatement::$statement
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$errno
	 * @uses MySQLiQuery::$insertid
	 * @uses MySQLiQueryStatement::$bind
	 * @uses MySQLiQueryStatement::$blob
	 * @uses MySQLiQuery::getQuery()
	 * @return true on success, else return false
	 */
	public function execute(){
		if(is_null($this->statement)){
			if(!$this->statement = $this->instance->prepare($this->getQuery())){
				$this->error = $this->instance->error;
				$this->errno = $this->instance->errno;
				return false;
			}
		}

		$bind = $this->bind['param'];
		array_unshift($bind, implode('', $this->bind['types']));
		call_user_func_array(array($this->statement, 'bind_param'), $bind);

		foreach ($this->blob as $key => $val){
			$this->statement->send_long_data($key, $val);
		}

		$this->statement->execute();
		$this->error = $this->statement->error;
		$this->errno = $this->statement->errno;
		$this->insertid = $this->statement->insert_id;
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQueryStatement::$statement
	 */
	public function getNumRows(){
		return $this->statement->num_rows;
	}
	/**
	 * {@inheritdoc}
	 *
	 * @uses MySQLiQueryStatement::$statement
	 * @return integer
	 */
	public function getAffectedRows(){
		return $this->statement->affected_rows;
	}

	/**
	 * {@inheritdoc}
	 *
	 * This method have not been implemented yet
	 *
	 * @todo Implement method
	 * @return boolean false
	 */
	public function fetchArray(){
		return false;
	}

	/**
	 * Destroy object.
	 *
	 * @uses MySQLiQueryStatement::$statement
	 */
	public function __destruct(){
		@$this->statement->close();
	}
}

class MySQLiTools {
	static public function spliceFields($field=null /*, [$field..] */){
		$fields = func_get_args();
		foreach ($fields as $val) {
			if(is_array($val)){
				foreach ($val as $subval){
					$freturn[] = $subval;
				}
			} else {
				$freturn[] = $val;
			}
		}
		return $freturn;
	}

	static public function parseNullValue($val){
		if(is_null($val)){
			$val = 'NULL';
		} else {
			$val = '\''.$val.'\'';
		}
		return $val;
	}
	static public function parseBooleanValue($val, $escape=true){
		if($escape){
			if($val === true){
				$val = '\'TRUE\'';
			} else {
				$val = '\'FALSE\'';
			}
		} else {
			if($val === true){
				$val = 'TRUE';
			} else {
				$val = 'FALSE';
			}
		}
		return $val;
	}
	static public function parseWildcards($val){
		return str_replace('*', '%', $val);
	}
	static public function parseUnixtimestamp($val,$statement=false){
		if($statement) {
			if(!is_null($val)) {
				return 'FROM_UNIXTIME(?)';
			} else {
				return null;
			}
		} else {
			if(!is_null($val)) {
				return 'FROM_UNIXTIME(\''.$val.'\')';
			} else {
				return 'NULL';
			}
		}
	}
	static public function prepareOrderStatement(DatabaseListHelperOrder $order){
		$args = func_get_args();
		$fields = array();
		array_shift($args);
		while (list(,$val) = each($args)) {
			if($arg = $order->get($val)){
				$fields[] = ''.$arg.'';
			}
		}
		if(sizeof($fields) > 0){
			return implode(', ', $fields);
		} else {
			return false;
		}
	}
	static public function prepareLimitStatement($offset=null, $limit=null){
		if(!is_null($offset) && !is_null(!$limit)){
			return 'LIMIT '.$offset.', '.$limit;
		} else if(!is_null($offset) && is_null($limit)){
			return 'OFFSET '.$offset;
		} else if(is_null($offset) && !is_null($limit)){
			return 'LIMIT '.$limit;
		} else {
			return false;
		}
	}

	static public function makeInsertStatement($table, array $fields){
		return 'INSERT INTO `'.$table.'` '.self::_makeInsertReplaceValues($fields);
	}
	static public function makeReplaceStatement($table, array $fields){
		return 'REPLACE INTO `'.$table.'` '.self::_makeInsertReplaceValues($fields);
	}
	static public function makeUpdateStatement($table, array $fields, $where=''){
		$query = 'UPDATE `'.$table.'`'."\n".' SET';
		$qfields = array();
		foreach ($fields as $field => $value){
			if(is_integer($field)){
				$qfields[] = ' `'.$value.'`=?';
			} else {
				$qfields[] = ' `'.$field.'`='.$value.'';
			}
		}
		return $query.' '.implode(', ', $qfields).' '.$where;
	}
	static public function makeInStatement(array $values){
		foreach ($values as $key => $val){
			if(!is_numeric($val)){
				$val = '\''.$val.'\'';
			}
			$values[$key] = $val;
		}
		return 'IN('.implode(', ', $values).')';
	}

	static protected function _makeInsertReplaceValues(array $fields){
		$qfields = array();
		$qvalues = array();
		foreach ($fields as $field => $value){
			if(is_integer($field)){
				$qfields[] = '`'.$value.'`';
				$qvalues[] = '?';
			} else {
				$qfields[] = '`'.$field.'`';
				$qvalues[] = $value;
			}
		}
		return '('.implode(', ', $qfields).')VALUES('.implode(', ', $qvalues).')';
	}
}
?>