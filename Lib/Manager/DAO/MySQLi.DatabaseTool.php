<?php
class MySQLi_DatabaseTool extends DatabaseDAO implements Singleton,DAO_DatabaseTool {
	private static $instance = null;
	
	const SELECT_COLUMNS = '';
	
	/**
	 *	@return MySQLi_DatabaseTool
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_DatabaseTool();
		}
		return self::$instance;	
	}

	public function getObjectsAndRevisions(){
		$query = 'SHOW TABLE STATUS';
		$query = $this->masterQuery(new MySQLiQuery($query));
		$status = array();
		while ($res = $query->fetchArray()) {
			if(preg_match('/Revision:\s+([0-9]+)/', $res['Comment'], $matches)){
				$status[$res['Name']] = $matches[1];
			} 
		}
		return $status;
	}
	public function getObjectsDependencies($data){
		$dependencies = array();
		if(preg_match_all('/REFERENCES\s.*?(\w+).*?\s\(/msi', $data, $matches)){
			$dependencies = array_merge($dependencies, $matches[1]);
		}
		return $dependencies;
	}
	
	public function performUpdate($data){
		foreach (preg_split('/;\s*$/m', $data) as $query){
			if(!empty($query)){
				$this->masterQuery(new MySQLiQuery($query));
			}
		}
	}
}
?>