<?php
abstract class DatabaseHelper {
	protected $settings = array();
	
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
	
}

abstract class DatabaseListHelper extends DatabaseHelper {
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
			return '`'.$column.'` '.$order;
		} else {
			return false;
		}
	}
}

class DatabaseListHelperFilter extends DatabaseListHelper {
	
}

class DatabaseDataHandler extends DatabaseHelper {
	private $special_values = array();
	private $updated_columns = array();
	
	public function set($column, $setting){
		parent::set($column, $setting);
		$this->updated_columns[$column] = $column;
	}
	
	public function getUpdatedColumns(){
		$special_values = array();
		
		foreach ($this->settings as $key => $val){
			if(isset($this->special_values[$key])){
				$special_values[$key] = $val;
				unset($this->settings[$key]);
			} else {
				$columns[] = $key;
			}
		}
		
		$special_keys = $this->special_values;
		foreach ($special_values as $key => $val){
			$this->settings[$key] = $val;
			$columns[$key] =  $special_keys[$key];
			unset($special_keys[$key]);
		}
		
		foreach ($special_keys as $key => $val){
			$columns[$key] = $val;
		}
		
		return $columns;
	}
	public function getUpdatedColumnValues(){
		return $this->settings;
	}
	
	public function isChanged($column){
		return isset($this->settings[$column]);
	}
	
	public function setSpecialValue($column, $value){
		$this->special_values[$column] = $value;
	}
}
?>