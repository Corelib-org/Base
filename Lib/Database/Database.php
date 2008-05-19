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

	public static function getDAO($class, $deprecated=null){
		if(!is_null($deprecated)){
			trigger_error('$lib is deprecated please update getDAO function', E_USER_NOTICE);
		}
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
	/**
	 * @param Query $query
	 * @return Query
	 */
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
	
	public function __construct($query) {
		$this->query = $query;
	}

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
		return $this->database->startTransaction();
	}
	public function commit(){
		return $this->database->commit();
	}
	public function rollback(){
		return $this->database->rollback();
	}
}


?>