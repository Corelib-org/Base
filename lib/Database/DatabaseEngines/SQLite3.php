<?php
/**
 * @todo Finish Engine SQLite3 engine
 */
class SQLite3Engine implements DatabaseEngine {
	private $filename = null;
	private $flags = null;
	private $key = null;
	private $charset = 'utf8';
	private $connection = null;
	
	const PREFIX = 'SQLite3';
	
	public function __construct($filename, $flags=null, $key=null, $charset='utf8'){
		$this->filename = $filename;
		$this->flags = $flags;
		$this->key = $key;
		$this->charset = $charset;
		$this->_connect();
	}
	
	public function query(Query $query){
		try {
			if(!$query instanceof SQLite3Query){
				throw new BaseException('Invalid Query Object, object must be instance of SQLite3Query');
			}
		} catch (BaseException $e){
			echo $e;
		}
			
		$query->setInstance($this->connection);
		return $query->execute();
	}
	
	public function getPrefix(){
		return self::PREFIX;
	}
	public function startTransaction(){
		$this->query(new SQLite3Query('BEGIN TRANSACTION'));
	}
	public function commit(){
		$this->query(new SQLite3Query('COMMIT'));
	}
	public function rollback(){
		$this->query(new SQLite3Query('ROLLBACK'));
	}
	
	private function _connect(){
		if(is_file($this->filename)){
			$this->connection = new SQLite3($this->filename, $this->flags, $this->key, $this->charset);
			return true;
		} else {
			return false;
		}
	}	
}
?>