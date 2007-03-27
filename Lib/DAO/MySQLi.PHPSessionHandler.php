<?php
class MySQLi_PHPSessionHandler extends DatabaseDAO implements Singleton,DAO_PHPSessionHandler {
	private static $instance = null;
	
	/**
	 *	@return Database
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_PHPSessionHandler();
		}
		return self::$instance;
	}
	
	public function read($session){
		$query = 'SELECT session_data
		          FROM tbl_sessions
		          WHERE session_id=\''.$session.'\' AND session_expire > NOW()';
		$query = $this->slaveQuery(new MySQLiQuery($query));
		if($out = $query->fetchArray()){
			return $out['session_data'];
		} else {
			return false;
		}
	}
	
	public function destroy($session){
		$query = 'DELETE FROM tbl_sessions
		          WHERE session_id=\''.$session.'\'';
		$query = $this->masterQuery(new MySQLiQuery($query));
	}
	
	public function update($session, $data, $expire){
		$query = 'REPLACE INTO tbl_sessions(session_id, session_data, session_expire)
		          VALUES(\''.$session.'\', "'.addslashes($data).'", FROM_UNIXTIME(\''.$expire.'\'))';
		$query = $this->masterQuery(new MySQLiQuery($query));
	}
	
	public function cleanup(){
		$query = 'DELETE FROM tbl_sessions
		          WHERE session_expire < NOW()';
		$query = $this->masterQuery(new MySQLiQuery($query));
	}
}
?>