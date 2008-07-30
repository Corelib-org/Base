<?php
if(!defined('DATABASE_SHOW_QUERY_LOG')){
	define('DATABASE_SHOW_QUERY_LOG', false);
}

class Database implements Singleton {
	private static $instance = null;
	private static $dao_prefix = null;

	private $slave = null;
	private $master = null;
	
	private $query_log = array();

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
		$this->_runQuery($this->master, $query);
//		$this->master->query($query);
//		$this->error($query);
		return $query;
	}

	public function slaveQuery(Query $query){
		$this->_runQuery($this->slave, $query);
//		$this->slave->query($query);
//		$this->error($query);
		return $query;
	}

	public function _runQuery(DatabaseEngine $instance, Query $query){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			$start = microtime(true);
			$instance->query($query);
			$e = new Exception();
			$this->query_log[] = array('query' => $query->getQuery(),
			                           'error' => array('code' => $query->getErrno(),
			                                            'message' => $query->getError()),
			                           'time' => round(microtime(true) - $start, 5),
			                           'backtrace' => $e->__toString());
		} else {
			$instance->query($query);
		}
		return $this->error($query);
	}
	
	private function error(Query $query){
		try {
			if($query->getErrno()){
				throw new BaseException($query->getError(), $query->getErrno());
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		return true;
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
	
	public function getQueryLog(){
		return $this->query_log;
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

class DatabasePrintStatsEvent implements EventTypeHandler,Observer  {
	private $subject = null;
	
	public function getEventType(){
		return 'EventRequestEnd';	
	}	
	public function register(ObserverSubject $subject){
		$this->subject = $subject;
	}
	public function update($update){
		$log = Database::getInstance()->getQueryLog();
		$duplicates = array();
		$duplicate_count = 0;
		$time = 0;
		$result = '';		
		foreach ($log as $key => $line){
			$result .= '<div>';
			$time += $line['time'];
			if(!isset($duplicates[md5($line['query'])])){
				$duplicates[md5($line['query'])] = ($key + 1);
				$result .= '<h2 onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }">#'.($key + 1).' Query ('.$line['time'].'s)';
			} else {
				$duplicate_count++;
				$result .= '<h2 onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }"><u>#'.($key + 1).' Query ('.$line['time'].'s) <b>WARNING: DUPLICATED QUERY (#'.$duplicates[md5($line['query'])].')</b></u>';
			}
			$result .= '<br/><span style="color: #999999; font-size: 10px;">'.substr($line['query'], 0, 200).'</span></h2>';
			
			if($line['error']['code'] > 0){
				$result .= '<h3>Error Code: '.$line['error']['code'].'</h3>';
				$result .= '<p>'.$line['error']['message'].'</p>';
			}
			
			$result .= '<div id="DatabaseQueryLog'.$key.'" style="display: none;"><h3>SQL</h3><pre>'.$line['query'].'</pre><br/>';
			$result .= '<h3>Backtrace</h3><pre>'.$line['backtrace'].'</pre><br/>';
			$result .= '<hr/><br/></div></div>';
		}
		
		echo '<div id="DatabaseQueryLog" style="text-align: left; width: 80%; margin: auto; background-color: #ffffef; padding: 20px;">';
		echo '<h1>Query Log (Queries: '.sizeof($log).', Duplicates: '.$duplicate_count.', Time: '.$time.'s )</h1>';
		echo $result;
		echo '</div">';
	}
}
if(DATABASE_SHOW_QUERY_LOG && BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
	$eventHandler = EventHandler::getInstance();
	$eventHandler->registerObserver(new DatabasePrintStatsEvent());
}
?>