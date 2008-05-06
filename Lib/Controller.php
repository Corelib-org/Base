<?php
abstract class Controller {
	protected $id;
	
	protected $dao = null;
	
	public function __construct($id = null, $array=array()){
		$this->id = $id;
		if(sizeof($array) > 0){
			$this->_setFromArray($array);
		} 
	}	
	
	abstract protected function _setFromArray($array); 
	abstract protected function commit();
	abstract protected function _getDAO($red=true);
	abstract protected function _create();
	abstract protected function _update();
	
	public function delete(){
		return $this->dao->delete($this->id);
	}
	public function read(){
		return $this->_read();
	}
		
	protected function _read(){
		$this->_getDAO(false);
		if($array = $this->dao->read($this->id)){
			$this->_setFromArray($array);
			return true;
		} else {
			return false;
		}
	}
}
?>