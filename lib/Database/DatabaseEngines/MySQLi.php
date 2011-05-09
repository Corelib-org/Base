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
 * @category corelib
 * @package Base
 * @subpackage Database
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */

//*****************************************************************//
//************** MySQLi Database Engine constants *****************//
//*****************************************************************//
/**
 * MySQLi Descending sort order key.
 *
 * @var string
 */
define('DATABASE_ORDER_DESC', 'DESC');

/**
 * MySQLi Ascending sort order key.
 *
 * @var string
 */
define('DATABASE_ORDER_ASC', 'ASC');


//*****************************************************************//
//***************** MySQLi Database Engine class ******************//
//*****************************************************************//
/**
 * MySQLi Database engine.
 *
 * The Database class provides all basic functionality,
 * for communicating with a mysql database using PHP's
 * mysqli extension.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class MySQLiEngine implements DatabaseEngine {


	//*****************************************************************//
	//************ MySQLi Database Engine class properties ************//
	//*****************************************************************//
	/**
	 * @var mysqli database connection
	 * @internal
	 */
	private $connection = null;
	/**
	 * @var string hostname
	 * @internal
	 */
	private $hostname = null;
	/**
	 * @var string username
	 * @internal
	 */
	private $username = null;
	/**
	 * @var string password
	 * @internal
	 */
	private $password = null;
	/**
	 * @var string database
	 * @internal
	 */
	private $database = null;
	/**
	 * @var string connection encoding
	 * @internal
	 */
	private $charset = 'utf8';
	/**
	 * @var boolean reconnect if connection is lost
	 * @internal
	 */
	private $reconnect = false;


	//*****************************************************************//
	//************ MySQLi Database Engine class constants *************//
	//*****************************************************************//
	/**
	 * MySQLi engine DAO class prefix.
	 *
	 * @var string prefix
	 */
	const PREFIX = 'MySQLi';


	//*****************************************************************//
	//************* MySQLi Database Engine class methods **************//
	//*****************************************************************//
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
	 * Start transaction.
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::startTransaction()
	 */
	public function startTransaction(){
		$this->query(new MySQLiQuery('START TRANSACTION'));
	}
	/**
	 * Commit transaction.
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::commit()
	 */
	public function commit(){
		$this->query(new MySQLiQuery('COMMIT'));
	}

	/**
	 * Rollback transaction.
	 *
	 * @uses MySQLiQuery
	 * @uses MySQLiEngine::query()
	 * @see DatabaseEngine::rollback()
	 */
	public function rollback(){
		$this->query(new MySQLiQuery('ROLLBACK'));
	}

	/**
	 * Analyse query.
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
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string.
	 */
	public function escapeString($string){
		if(is_null($this->connection)){
			$this->_connect();
		}
		return $this->connection->real_escape_string($string);
	}

	/**
	 * Convert unix timestamp to database timestamp.
	 *
	 * @param integer $timestamp unix timestamp to convert
	 * @return string database compatible timestamp
	 */
	public function createTimestamp($timestamp){
		return strftime('%Y-%m-%d %X', $timestamp);
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
	 * @internal
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


//*****************************************************************//
//********************** MySQLiQuery class ************************//
//*****************************************************************//
/**
 * MySQLi Query.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class MySQLiQuery extends Query {

	//*****************************************************************//
	//**************** MySQLiQuery class properties *******************//
	//*****************************************************************//
	/**
	 * @var mysqli
	 * @internal
	 */
	protected $instance = null;
	/**
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
	 * @uses MySQLiQuery::$instance
	 * @uses MySQLiQuery::$result
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$errno
	 * @uses MySQLiQuery::$insertid
	 * @uses MySQLiQuery::getQuery()
	 * @internal
	 */
	public function execute(){
		$this->result = $this->instance->query($this->getQuery());
		$this->error = $this->instance->error;
		$this->errno = $this->instance->errno;
		$this->insertid = $this->instance->insert_id;
		$this->affected_rows = $this->instance->affected_rows;
	}

	/**
	 * Set connection instance.
	 *
	 * @uses MySQLiQuery::$instance
	 * @internal
	 */
	public function setInstance($instance){
		$this->instance = $instance;
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
	 * @return array
	 */
	public function fetchArray(){
		return $this->result->fetch_array();
	}

	/**
	 * Fetch row as array
	 *
	 * @uses MySQLiQuery::$result
	 * @return array
	 */
	public function fetchRow(){
		return $this->result->fetch_row();
	}

	/**
	 * Fetch row as an associative array
	 *
	 * @uses MySQLiQuery::$result
	 * @return array
	 */
	public function fetchAssoc(){
		return $this->result->fetch_assoc();
	}

	/**
	 * Fetch fields as array.
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
	 * @uses MySQLiQuery::$result
	 * @return array
	 */
	public function dataSeek($offset){
		return $this->result->data_seek($offset);
	}

	/**
	 * @see MySQLiQuery::getQuery()
	 * @return string query
	 * @internal
	 */
	public function __toString(){
		return $this->getQuery();
	}
}


//*****************************************************************//
//***************** MySQLiQueryStatement class ********************//
//*****************************************************************//
/**
 * MySQLi Query Statement.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class MySQLiQueryStatement extends MySQLiQuery {


	//*****************************************************************//
	//************ MySQLiQueryStatement class properties **************//
	//*****************************************************************//
	/**
	 * @var mysqli_stmt Mysqli stmt instance
	 * @internal
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

	private static $statements = array();

	//*****************************************************************//
	//************** MySQLiQueryStatement class methods ***************//
	//*****************************************************************//
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
	 * @uses MySQLiQuery::$instance
	 * @uses MySQLiQueryStatement::$statement
	 * @uses MySQLiQuery::$error
	 * @uses MySQLiQuery::$errno
	 * @uses MySQLiQuery::$insertid
	 * @uses MySQLiQueryStatement::$bind
	 * @uses MySQLiQueryStatement::$blob
	 * @uses MySQLiQuery::getQuery()
	 * @return true on success, else return false
	 * @internal
	 */
	public function execute(){
		if(is_null($this->statement)){
			$query = $this->getQuery();
			if(isset(self::$statements[$query])){
				$this->statement = self::$statements[$query];
			} else {
				if(!$this->statement = $this->instance->prepare($query)){
					$this->error = $this->instance->error;
					$this->errno = $this->instance->errno;
					return false;
				}
				self::$statements[$query] = $this->statement;
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
	 * Fetch row as array from result.
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
	 * @internal
	 */
	public function __destruct(){
		@$this->statement->free_result();
	}
}


//*****************************************************************//
//********************** MySQLiTools class ************************//
//*****************************************************************//
/**
 * MySQLi Tools.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class MySQLiTools {


	//*****************************************************************//
	//******************* MySQLiTools class methods *******************//
	//*****************************************************************//
	/**
	 * Splice any number of fields together.
	 *
	 * Combine each paramater into one array of all fields.
	 * Any number of parameters can be supplied to this method.
	 * either a string with a field name or a array with multiple
	 * field names.
	 *
	 * @param mixed $field string or array with field names.
	 * @return array
	 */
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

	/**
	 * Parse NULL value.
	 *
	 * Parse php datatype "null" into a valid mysql statement.
	 * if value is not null the correct format will also be returned.
	 *
	 * @param mixed $value data
	 * @return string
	 */
	static public function parseNullValue($value){
		if(is_null($value)){
			$value = 'NULL';
		} else {
			$value = '\''.$value.'\'';
		}
		return $value;
	}

	/**
	 * Parse bolean value
	 *
	 * Parse PHP datatype "boolean" into valid mysql statement, and
	 * return a string to be used with the enum boolean principle used
	 * by the {@link CodeGenerator}.
	 *
	 * @param string $value
	 * @param boolean $escape if true escape string, else do nothing.
	 * @return string
	 */
	static public function parseBooleanValue($value, $escape=true){
		if($escape){
			if($value === true){
				$value = '\'TRUE\'';
			} else {
				$value = '\'FALSE\'';
			}
		} else {
			if($value === true){
				$value = 'TRUE';
			} else {
				$value = 'FALSE';
			}
		}
		return $value;
	}

	/**
	 * Replace * wildcards with mysql % wildcard.
	 *
	 * @param string $value
	 * @return string
	 */
	static public function parseWildcards($value){
		return str_replace('*', '%', $value);
	}

	/**
	 * Parse unix timestamp into mysql "unix timestamp"
	 *
	 * Convert timestamp to a valid mysql unix timestamp statement
	 * using mysql's {@link FROM_UNIXTIME http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_from-unixtime} function
	 *
	 * @param integer $value unix timestamps
	 * @param boolean $statement if true prepare timestamp for a mysql statement.
	 * @return string
	 */
	static public function parseUnixtimestamp($value,$statement=false){
		if($statement) {
			if(!is_null($value)) {
				return 'FROM_UNIXTIME(?)';
			} else {
				return null;
			}
		} else {
			if(!is_null($value)) {
				return 'FROM_UNIXTIME(\''.$val.'\')';
			} else {
				return 'NULL';
			}
		}
	}

	/**
	 * Prepare order statement
	 *
	 * Convert {@link DatabaseListHelperOrder} into a valid mysql order statement.
	 *
	 * @param DatabaseListHelperOrder $order
	 * @return mixed false if no ordering should be done, else return string with order statement.
	 */
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

	/**
	 * Prepare limit statement.
	 *
	 * Convert limit and offset into a valid mysql statement
	 *
	 * @param integer $offset
	 * @param integer $limit
	 * @return string
	 */
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

	/**
	 * Create prepared statement compatible insert statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $param extra parameters
	 * @return string
	 */
	static public function makeInsertStatement($table, array $fields, $param='', $options=''){
		return 'INSERT '.$options.' INTO `'.$table.'`  '.self::_makeInsertReplaceValues($fields).' '.$param;
	}

	/**
	 * Create prepared statement compatible replace statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @return string
	 */
	static public function makeReplaceStatement($table, array $fields){
		return 'REPLACE INTO `'.$table.'` '.self::_makeInsertReplaceValues($fields);
	}

	/**
	 * Create prepared statement compatible update statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $where where statement
	 * @return string
	 */
	static public function makeUpdateStatement($table, array $fields, $where=''){
		$query = 'UPDATE `'.$table.'`'."\n".' SET';
		return $query.' '.self::makeUpdateColumns($fields).' '.$where;
	}

	/**
	 * Convert array to valid mysql update column list.
	 *
	 * @param array $fields
	 * @return string
	 */
	static public function makeUpdateColumns(array $fields){
		$qfields = array();
		foreach ($fields as $field => $value){
			if(is_integer($field)){
				$qfields[] = ' `'.$value.'`=?';
			} else {
				$qfields[] = ' `'.$field.'`='.$value.'';
			}
		}
		return implode(', ', $qfields);
	}

	/**
	 * Convert several values into a mysql IN statement.
	 *
	 * @param array $values
	 * @return string
	 */
	static public function makeInStatement(array $values){
		foreach ($values as $key => $val){
			if(!is_numeric($val)){
				$val = '\''.$val.'\'';
			}
			$values[$key] = $val;
		}
		return 'IN('.implode(', ', $values).')';
	}

	/**
	 * Create insert/replace values.
	 *
	 * @param array $fields
	 * @return string
	 */
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