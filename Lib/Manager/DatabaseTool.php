<?php
interface DAO_DatabaseTool {
	public function getObjectsAndRevisions();
	public function getObjectsDependencies($data);
}
class DatabaseTool implements Output {
	private $objects = array();
	private $order = array();
	/**
	 * @var DAO_DatabaseTool
	 */
	private $dao = null;
	private function _findDatabaseScripts(){
		$config = ManagerConfig::getInstance();
		$database = Database::getPrefix();
		$registry = $config->getPropertyXML('database');
		$this->_getDAO();
		
		echo '<pre>';
				
		foreach ($this->dao->getObjectsAndRevisions() as $object => $revision){
			$this->objects[$object]['current'] = $revision;
		}
		
		$xpath = new DOMXPath($registry->ownerDocument);
		$xpath = $xpath->query('engine[@id = "'.$database.'"]/scripts', $registry);
		for ($i = 0; $item = $xpath->item($i); $i++){
			$this->_findFiles(trim(Manager::parseConstantTags($item->nodeValue)));
		}

		foreach ($this->objects as $object => $actions){
			if(!isset($actions['upgrades']) && !isset($actions['create'])){
				unset($this->objects[$object]);
			}
		}
		
		foreach ($this->objects as $object => $data){
			if(!in_array($object, $this->order)){
				$this->_findDependencies($object);
			}
		}
		$actions = array();
		foreach ($this->order as $object){
			$actions = array_merge($actions, $this->objects[$object]['actions']);
		}
		
		foreach ($actions as $action){
			echo $action.'<br/><br/><br/>';	
		}
	}
	
	private function _findDependencies($object){
		$dependecies = array();
		if(isset($this->objects[$object]['upgrades'])){
			foreach ($this->objects[$object]['upgrades'] as $data){
				$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($data));
				$this->objects[$object]['actions'][] = $data;
			}
		}
		if(isset($this->objects[$object]['create'])){
			$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($this->objects[$object]['create']['filename']));
			$this->objects[$object]['actions'][] = $this->objects[$object]['create']['filename'];
		}
		foreach ($dependecies as $dependecy){
			if(!in_array($dependecy, $this->order) && isset($this->objects[$dependecy])){
				$this->_findDependencies($dependecy);
			}
		}
		$this->order[] = $object;
	}

	private function _addObject($filename){
		if(preg_match('/^(.*?)\.(.*?)\./', basename($filename), $matches)){
			$object = trim($matches[1]);
			if(strstr($matches[2], '-')){
				list($revision, $head) = explode('-', $matches[2], 2);
				if(isset($this->objects[$object]) && $head > $this->objects[$object]['current']){
					$this->objects[$object]['upgrades'][$head] = file_get_contents($filename);
				}
			} else if(!isset($this->objects[$object]) || (isset($this->objects[$object]['create']) && $this->objects[$object]['create']['revision'] < $matches[2])){
				$this->objects[$object]['create'] = array('revision' => $matches[2], 'filename' => file_get_contents($filename));
			}
		}
	}
	
	private function _findFiles($dir){
		if(substr($dir, 0, -1) != '/'){
			$dir = $dir.'/';
		}
		if(is_dir($dir) && is_readable($dir)){
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if($entry{0} != '.' && is_file($dir.$entry)){
					$this->_addObject($dir.$entry);
				} else if ($entry{0} != '.' && is_dir($dir.$entry)){
					$this->_findFiles($dir.$entry);
				}
			}
		}		
	}
	
	public function getXML(DOMDocument $xml){
		$this->_findDatabaseScripts();
		exit;
	}
	
	public function &getArray(){
		
	}
	
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('DatabaseTool');
		}
		return true;
	}	
}
?>