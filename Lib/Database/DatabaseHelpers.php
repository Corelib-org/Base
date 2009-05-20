<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database helpers
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is 
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * test   
 * 
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Database
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id: Database.php 5008 2009-05-19 14:29:10Z wayland $)
 * @filesource
 */



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
				
				if(!$arg || in_array($key, $arg) || isset($arg[$key])){
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