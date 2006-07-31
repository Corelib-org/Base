<?php
class Database implements Singleton {
	private static $instance = null;
	private static $dao_prefix = null;
	
	private $slave = null;
	private $master = null;
	
	private function __construct(){
		
	}
	/**
	 *	@return Database
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Database();
		}
		return self::$instance;	
	}
	
	public static function getDAO($lib, $class){
		$eval = 'return '.self::$dao_prefix.'_'.$class.'::getInstance();';
		return eval($eval);
	}
	
	public static function getAlternateDAO($folder, $class){
		include_once($folder.'/'.self::$dao_prefix.'.'.$class.'.php');	
		$eval = 'return '.self::$dao_prefix.'_'.$class.'::getInstance();';
		return eval($eval);
	}
	
	public function masterConnect(DatabaseEngine $master){
		$this->master = $master;
		self::$dao_prefix = $this->master->getPrefix();
		if(is_null($this->slave)){
			$this->slave = $master;
		}	
	}
	
	public function slaveConnect(DatabaseEngine $slave){
		$this->slave = $slave;
	}
	
	public function query(Query $query){
		if(preg_match('/INSERT|SELECT INTO|UPDATE|MERGE|DELETE|TRUNCATE/', $query->getQuery())){
			$this->masterQuery($query);	
		} else {
			$this->slaveQuery($query);	
		}
		$this->error($query);
		return $query;
	}
	
	public function masterQuery(Query $query){
		$this->master->query($query);
		$this->error($query);
		return $query;
	}
	
	public function slaveQuery(Query $query){
		$this->slave->query($query);
		$this->error($query);
		return $query;
	}
	
	private function error(Query $query){
		echo 'MYERROR: ';var_dump($query->getErrno());
		try {
			if($query->getErrno()){
				throw new BaseException($query->getError(), $query->getErrno());
			}
		} catch (BaseException $e){
			echo $e;
		}
	}
	
	public static function getPrefix(){
		return self::$dao_prefix;
	}
	
	public function startTransaction(){
		$this->master->startTransaction();
	}
	public function commit(){
		$this->master->commit();
	}
	public function rollback(){
		$this->master->rollback();
	}
}

interface DatabaseEngine {
	public function query(Query $query);
	public function getPrefix();
	public function startTransaction();
	public function commit();
	public function rollback();
}

abstract class Query {
	protected $query = null;
	protected $count = null;
	protected $numberOflimit = null;
	
	public function __construct($query, $limit=null, $limitOffset=null, $order=null, $orderType=null, $countKey=null) {
		$this->query = $query;
		if(!is_null($countKey))
			$this->getCount($countKey);	
		if(!is_null($order))
			$this->setOrder($order,$orderType);		
		if(!is_null($limit))
			$this->setLimit($limit,$limitOffset);
	}
	public function getCount($countKey) {
		if(is_null($this->count)){
			$this->count = $this->doCount($countKey);
			return $this->count;
		} else {
			return $this->count;
		}
	}
	abstract public function setLimit($limit=null,$limitOffset=null);
	abstract public function setOrder($order=null,$orderType=null);
	abstract protected function doCount($countKey);
	
	abstract public function execute();
	abstract public function getQuery();	
	abstract public function getError();
	abstract public function getErrno();
	abstract public function setInstance($instance);
	abstract public function getNumRows();
	abstract public function getInsertID();
	abstract public function fetchArray();
	abstract public function getAffectedRows();
}

abstract class DatabaseDAO {
	private $database;
	
	final protected function __construct(){
		$this->database = Database::getInstance();	
	}
	
	final protected function query(Query $query){
		return $this->database->query($query);	
	}
	/**
	 * Enter description here...
	 *
	 * @param Query $query
	 * @return Query
	 */
	final protected function masterQuery(Query $query){
		return $this->database->masterQuery($query);	
	}
	/**
	 * Enter description here...
	 *
	 * @param Query $query
	 * @return Query
	 */	
	final protected function slaveQuery(Query $query){
		return $this->database->slaveQuery($query);	
	}
	
	public function startTransaction(){
		$this->database->startTransaction();
	}
	public function commit(){
		$this->database->commit();
	}
	public function rollback(){
		$this->database->rollback();
	}	
}
?>