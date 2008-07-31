<?php
define('DATABASE_ORDER_DESC', 'DESC');
define('DATABASE_ORDER_ASC', 'ASC');
define('DATABASE_GT', '>');
define('DATABASE_LT', '<');
define('DATABASE_EQUAL', '=');

class MySQLiEngine implements DatabaseEngine {
	private $connection = null;
	private $hostname = null;
	private $username = null;
	private $password = null;
	private $database = null;
	private $charset = 'utf8';
	private $pid = null;
	private $reconnect = false;

	const PREFIX = 'MySQLi';

	public function __construct($hostname, $username, $password, $database, $reconnect=false, $charset='utf8'){
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->pid = posix_getpid();
		$this->reconnect = $reconnect;;
		$this->charset = $charset;
	}
	public function query(Query $query){
		try {
			if(!$query instanceof MySQLiQuery){
				throw new BaseException('Invalid Query Object, object must be instance of MySQLiQuery');
			}
		} catch (BaseException $e){
			echo $e;
		}
		if(function_exists('posix_getpid')){
			if($this->pid != posix_getpid() || is_null($this->connection)){
				$this->_connect();
			}
		} else {
			if(is_null($this->connection)){
				$this->_connect();
			}			
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
	public function getPrefix(){
		return self::PREFIX;
	}
	public function startTransaction(){
		$this->query(new MySQLiQuery('START TRANSACTION'));
	}
	public function commit(){
		$this->query(new MySQLiQuery('COMMIT'));
	}
	public function rollback(){
		$this->query(new MySQLiQuery('ROLLBACK'));
	}
	
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

class MySQLiQuery extends Query {
	private $instance = null;
	private $result = null;
	private $error = null;
	private $errno = null;
	private $insertid = null;

	public function __construct($query){
		parent::__construct($query);
	}
	public function execute(){
		$this->result = $this->instance->query($this->getQuery());
		$this->error = $this->instance->error;
		$this->errno = $this->instance->errno;
		$this->insertid = $this->instance->insert_id;
	}
	public function setInstance($instance){
		$this->instance = $instance;
	}
	public function getQuery(){
		return $this->query;
	}
	public function getError(){
		return $this->error."\n<br/><br/>".$this->query;
	}
	public function getErrno(){
		return $this->errno;
	}
	public function getNumRows(){
		return $this->result->num_rows;
	}
	public function getInsertID(){
		return $this->insertid;
	}
	public function fetchArray(){
		return $this->result->fetch_array();
	}
	public function getAffectedRows(){
		return $this->instance->affected_rows;
	}
	public function __toString(){
		return $this->getQuery();
	}
}

class MySQLiTools {
	static public function parseNullValue($val){
		if(is_null($val)){
			$val = 'NULL';
		} else {
			$val = '\''.$val.'\'';
		}
		return $val;
	}
	static public function parseBooleanValue($val){
		if($val === true){
			$val = '\'TRUE\'';
		} else {
			$val = '\'FALSE\'';
		}
		return $val;
	}	
	static public function parseWildcards($val){
		return str_replace('*', '%', $val);
	}
	static public function parseUnixtimestamp($val){
		return 'FROM_UNIXTIME(\''.$val.'\')';
	}
	static public function prepareOrderStatement(DatabaseListHelperOrder $order){
		$args = func_get_args();
		$fields = array();
		array_shift($args);
		while (list(,$val) = each($args)) {
			if($arg = $order->get($val)){
				$fields[] = $arg;
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
		return 'INSERT INTO '.$table.' '.self::_makeInsertReplaceValues($fields);
	}
	
	static public function makeReplaceStatement($table, array $fields){
		return 'REPLACE INTO '.$table.' '.self::_makeInsertReplaceValues($fields);
	}
	
	static protected function _makeInsertReplaceValues(array $fields){
		$qfields = array();
		$qvalues = array();
		foreach ($fields as $field => $value){
			$qfields[] = $field;
			$qvalues[] = $value;
		}
		return '('.implode(', ', $qfields)."\n".'VALUES('.implode(', ', $qvalues).')';
	}
}
 
?>