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
	private $special_exclude = array();
	private $history_values = array();
	
	public function set($column, $setting, $history=null){
		parent::set($column, $setting);
		$this->updated_columns[$column] = $column;
		if(!is_null($history)){
			$this->history_values[$column] = $history;
		}
	}
	
	public function getHistoryValue($column){
		if(isset($this->history_values[$column])){
			return $this->history_values[$column];
		} else {
			return false;
		}
	}
	
	public function getUpdatedColumns(){
		$special_values = array();
		
		foreach ($this->settings as $key => $val){
			
			if(isset($this->special_values[$key]) && !in_array($key, $this->special_exclude)){
				
				$special_values[$key] = $val;
				unset($this->settings[$key]);
			} else if(!in_array($key, $this->special_exclude)){
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
		$values = array();
		foreach ($this->settings as $key => $val){
			if(!in_array($key, $this->special_exclude)){
				$values[$key] = $val;
			}
		}
		return $values;
	}
	
	public function isChanged($column){
		return isset($this->settings[$column]);
	}
	
	public function setSpecialValue($column, $value){
		$this->special_values[$column] = $value;
	}
	
	public function addExcludeField($column){
		$this->special_exclude[] = $column;
	}
}
?>