<?php
class MySQLiEngine implements DatabaseEngine {
	private $connection = null;
	private $hostname = null;
	private $username = null;
	private $password = null;
	private $database = null;
	private $pid = null;

	const PREFIX = 'MySQLi';
	
	public function __construct($hostname, $username, $password, $database){
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->pid = posix_getpid();
//		$this->_connect();
/*		$this->query(new MySQLiQuery('SET character_set_client = x'));
		$this->query(new MySQLiQuery('SET character_set_results = x'));
		$this->query(new MySQLiQuery('SET collation_connection = @@collation_database')); */
	}
	
	private function _connect(){
		$this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
		
	}
	
	public function query(Query $query){
		try {
			if(!$query instanceof MySQLiQuery){
				throw new BaseException('Invalid Query Object, object must be instance of MySQLiQuery');	
			}
		} catch (BaseException $e){
			echo $e;	
		}
		if($this->pid != posix_getpid() || is_null($this->connection) || !$this->connection->ping()){
			$this->_connect();
		}
		$query->setInstance($this->connection);
		while(true){
			$query->execute();
			switch ($query->getErrno()){
				case 2013 || 2006 || 2002 || 2003:
					sleep(5);
					echo 'Attempting to reconnect'."\n\n";
					$this->_connect();
					$query->setInstance($this->connection);
					break;
				default:
					return true;
					break;
			}
		}
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
	
}

class MySQLiQuery extends Query {
	private $instance = null;
	private $result = null;
	private $error = null;
	private $errno = null;
	private $insertid = null;
	
	public function __construct($query, $limit=null, $limitOffset=null, $order=null, $orderType=null, $count=null){
		parent::__construct($query, $limit, $limitOffset, $order, $orderType, $count);
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
	public function setLimit($limit=null,$limitOffset=null) {
		if(!is_null($limit)) {
			if(is_null($limitOffset)) {
				$limitOffset = 0;
			}
			if(!is_null($this->count))
				$this->numberOflimit = ceil($this->_count/$limit);
			$this->query .= ' LIMIT '.$limitOffset.','.$limit;
		}
	}
	public function setOrder($order=null,$orderType=null) {
		if(!is_null($order)) {
			$this->query .= ' ORDER by ' . $order;
			if(strtoupper($orderType) == 'DESC') {
				$this->query .= ' DESC';
			}	
		}
	}	
	protected function doCount($countKey) {
		$pos_to = strlen($this->query);
		$pos_from = stripos($this->query, ' from', 0);
		
		$pos_group_by = stripos($this->query, ' group by', $pos_from);
		if (($pos_group_by < $pos_to) && ($pos_group_by != false)) 
			$pos_to = $pos_group_by;
		
		$pos_having = stripos($this->query, ' having', $pos_from);
		if (($pos_having < $pos_to) && ($pos_having != false)) 
			$pos_to = $pos_having;
		
		$pos_order_by = stripos($this->query, ' order by', $pos_from);
		if (($pos_order_by < $pos_to) && ($pos_order_by != false)) 
			$pos_to = $pos_order_by;		
		
		if (stripos($this->query, 'distinct') || stripos($this->query, 'group by')) {
			$count_string = 'distinct ' . $countKey;
		} else {
			$count_string = $countKey;
		}
		$count_query = "SELECT COUNT(" . $count_string . ") AS count " . substr($this->query, $pos_from, ($pos_to - $pos_from));
		
		/*
		$result = $this->instance->query($count_query);
		
		$out = $result->fetch_array();	
		if(isset($out['count']) && !is_null($out['count'])){
			return $out['count'];
		} else {
			return null;
		}
		*/
	}	
}
?>