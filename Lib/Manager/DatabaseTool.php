<?php
interface DAO_DatabaseTool {
	public function getObjectsAndRevisions();
	public function getObjectsDependencies($data);
	public function performUpdate($data);
}
class DatabaseTool implements Output {
	private $objects = array();
	private $order = array();
	private $excludes = array();
	/**
	 * @var DAO_DatabaseTool
	 */
	private $dao = null;
	
	public function setExcludes($item=null /*, [$items...] */){
		$this->excludes = func_get_args();
	}
	
	public function update(){
		$this->_getDAO();
		$objects = $this->_findDatabaseScripts();
		foreach ($objects as $object => $actions){
			foreach ($actions['actions'] as $object => $action){
				$this->dao->performUpdate($action['action']);
			}
		}
	}
	
	public function getXML(DOMDocument $xml){
		$objects = $this->_findDatabaseScripts();
		$updates = $xml->createElement('database');
		
		foreach ($objects as $object => $actions){
			$objectXML = $updates->appendChild($xml->createElement('object'));
			$objectXML->setAttribute('name', $object);
			if(sizeof($actions['dependencies'])){
				$dependencies = $objectXML->appendChild($xml->createElement('dependencies'));
				foreach ($actions['dependencies'] as $dependency){
					$dependencies->appendChild($xml->createElement('dependency', $dependency));
				}
			}
			$actionsXML = $objectXML->appendChild($xml->createElement('actions'));
			foreach ($actions['actions'] as $object => $action){
				$update = $actionsXML->appendChild($xml->createElement('action', $action['action']));
				$update->setAttribute('type', $action['type']);
			}
		}
		return $updates;
	}
	
	public function &getArray(){
		
	}	
	
	private function _findDatabaseScripts(){
		$config = ManagerConfig::getInstance();
		$database = Database::getPrefix();
		$registry = $config->getPropertyXML('database');
		$this->_getDAO();
		foreach ($this->dao->getObjectsAndRevisions() as $object => $revision){
			$this->objects[$object]['current'] = $revision;
		}
		
		
		$xpath = new DOMXPath($registry->ownerDocument);
		$xpath = $xpath->query('engine[@id = "'.$database.'"]/scripts', $registry);
		for ($i = 0; $item = $xpath->item($i); $i++){
			$this->_findFiles(trim(Manager::parseConstantTags($item->nodeValue)));
		}
		
		foreach ($this->objects as $object => $data){
			if(!in_array($object, $this->order)){
				$this->_findDependencies($object);
			}
		}
		$actions = array();
		foreach ($this->order as $object){
			if(isset($this->objects[$object]['actions']) && !in_array($object, $this->excludes)){
				if(!isset($actions[$object])){
					$actions[$object]['actions'] = array();
					$actions[$object]['dependencies'] = array();
				} 
				$actions[$object]['actions'] = array_merge($actions[$object]['actions'], $this->objects[$object]['actions']);
				if(isset($this->objects[$object]['dependencies'])){
					$actions[$object]['dependencies'] = array_merge($actions[$object]['dependencies'], $this->objects[$object]['dependencies']);
				}
				//$actions = array_merge($actions, $this->objects[$object]['actions']);
			}
		}
		return $actions;
		/*
		foreach ($actions as $action){
			echo $action.'<br/><br/><br/>';	
		}*/
	}
	
	private function _findDependencies($object){
		$dependecies = array();

		if(isset($this->objects[$object]['upgrades'])){
			foreach ($this->objects[$object]['upgrades'] as $data){
				$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($data));
				$this->objects[$object]['actions'][] = array('type' => 'update', 'action' => $data);
			}
		}
		if(isset($this->objects[$object]['create'])){
			$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($this->objects[$object]['create']['filename']));
			$this->objects[$object]['actions'][] = array('type' => 'create', 'action' => $this->objects[$object]['create']['filename']);
		}
		$resolved = true;
		
		foreach ($dependecies as $dependecy){
			$this->objects[$object]['dependencies'][] = $dependecy;
			if(!in_array($dependecy, $this->order) && isset($this->objects[$dependecy]) && $object != $dependecy){
				$this->_findDependencies($dependecy);
			} else if(!isset($this->objects[$dependecy])){
				 $resolved = false;
			}
		}

		if($resolved){
			$this->order[] = $object;
		}
	}

	private function _addObject($filename){
		if(preg_match('/^(.*?)\.(.*?)\./', basename($filename), $matches)){
			$object = trim($matches[1]);
			if(strstr($matches[2], '-')){
				list($revision, $head) = explode('-', $matches[2], 2);
				if(isset($this->objects[$object]['current']) && $head > $this->objects[$object]['current']){
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
	
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('DatabaseTool');
		}
		return true;
	}	
}
?>