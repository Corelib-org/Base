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
	
	public function getUpdatedColumns($column=null /*[,$column..]*/){
		$columns = array();
		$special_values = array();
		
		$arg = func_get_args();
		if(count($arg) == 0){
			$arg = false;
		}
		foreach ($this->settings as $key => $val){
			if(isset($this->special_values[$key]) && (!in_array($key, $this->special_exclude) || ($arg !== false && in_array($key, $arg)))){
				$special_values[$key] = $val;
				unset($this->settings[$key]);
			} else if(!in_array($key, $this->special_exclude) || ($arg !== false && in_array($key, $arg))){ 
				if(!$arg || in_array($key, $arg)){
					$columns[] = $key;
				}
			}
		}
		
		$special_keys = $this->special_values;
		foreach ($special_values as $key => $val){
			$this->settings[$key] = $val;
			if(!$arg || in_array($key, $arg)){
				$columns[$key] =  $special_keys[$key];
			}
			unset($special_keys[$key]);
		}
		
		foreach ($special_keys as $key => $val){
			if(!$arg || in_array($key, $arg)){
				$columns[$key] = $val;
			}
		}
		return $columns;
	}
	public function getUpdatedColumnValues($column=null /*[,$column..]*/){
		$values = array();
		
		if(!is_array($column)){
			$arg = func_get_args();
			if(count($arg) == 0){
				$arg = false;
			}
		} else {
			$arg = $column;
		}
		
		foreach ($this->settings as $key => $val){
			if(!in_array($key, $this->special_exclude) || ($arg !== false && in_array($key, $arg))){
				if(!$arg || in_array($key, $arg)){
					$values[$key] = $val;
				}
			}
		}
		return $values;
	}
	
	public function isChanged($column = null){
		if(is_null($column)){
			if(count($this->settings) > 0){
				return true;
			} else {
				return false;
			}
		} else {
			return isset($this->settings[$column]);
		}
	}
	
	public function setSpecialValue($column, $value){
		$this->special_values[$column] = $value;
	}
	
	public function addExcludeField($column){
		$this->special_exclude[] = $column;
	}
}
?>