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
		/*
		if(function_exists('posix_getpid')){
			if($this->pid != posix_getpid() || is_null($this->connection)){
				$this->_connect();
			}
		} else { */
			if(is_null($this->connection)){
				$this->_connect();
			}			
		// }
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
	protected $instance = null;
	protected $result = null;
	protected $error = null;
	protected $errno = null;
	protected $insertid = null;

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

class MySQLiQueryStatement extends MySQLiQuery {
	/**
	 * @var mysqli_stmt
	 */
	private $statement = null;
	private $bind = array();
	private $blob = array();
	
	public function __construct($query, $item=null /*, [$items...] */){
		parent::__construct($query);
		$bind = func_get_args();
		if(sizeof($bind) > 0){
			array_shift($bind);
			call_user_func_array(array($this, 'bind'), $bind);
		}
	}
	
	public function bind($item=null /*, [$items...] */){
		$this->bind = array();
		$bind = func_get_args();
		
		
		foreach ($bind as $key => $val) {
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
	}
	
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
	}
	
	public function getNumRows(){
		return $this->statement->num_rows;
	}	
	
	public function getAffectedRows(){
		return $this->statement->affected_rows;
	}	
	
	public function fetchArray(){
		return false;
	}
	
	public function __destruct(){
		@$this->statement->close();
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
	
	static public function makeInsertStatement($table, array $fields, $statement=false){
		return 'INSERT INTO '.$table.' '.self::_makeInsertReplaceValues($fields, $statement);
	}
	
	static public function makeReplaceStatement($table, array $fields, $statement=false){
		return 'REPLACE INTO '.$table.' '.self::_makeInsertReplaceValues($fields, $statement);
	}
	
	static public function makeUpdateStatement($table, array $fields, $where='', $statement=false){
		$query = 'UPDATE '.$table."\n".' SET';
		$qfields = array();
		foreach ($fields as $field => $value){
			if($statement || is_integer($field)){
				$qfields[] = ' '.$value.'=?';
			} else {
				$qfields[] = ' '.$field.'='.$value.'';
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
	
	static protected function _makeInsertReplaceValues(array $fields, $statement=false){
		$qfields = array();
		$qvalues = array();
		foreach ($fields as $field => $value){
			if($statement || is_integer($field)){
				$qfields[] = $value;
				$qvalues[] = '?';
			} else {
				$qfields[] = $field;
				$qvalues[] = $value;
			}
		}
		return '('.implode(', ', $qfields).')VALUES('.implode(', ', $qvalues).')';
	}
} 
?>
