<?php
abstract class DatabaseListHelper {
	private $settings = array();
	
	public function set($column, $setting){
		$this->settings[$column] = $setting;
	}
	
	public function get($column){
		if(isset($this->settings[$column])){
			return $this->settings[$column];
		} else {
			return null;
		}
	}
	
	public function count(){
		return sizeof($this->settings);
	}
}

class DatabaseListHelperOrder extends DatabaseListHelper {
	public function set($column, $setting=DATABASE_ORDER_DESC){
		parent::set($column, $setting);
	}
	
	public function get($column){
		if($order = parent::get($column)){
			return $column.' '.$order;
		} else {
			return false;
		}
	}
}

class DatabaseListHelperFilter extends DatabaseListHelper {
	
}
?>