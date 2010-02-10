<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database helpers.
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
 * @category corelib
 * @package Base
 * @subpackage Database
 *
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */


//*****************************************************************//
//*********************** DatabaseHelper class ********************//
//*****************************************************************//
/**
 * DatabaseHelper class.
 *
 * The DatabaseHelper class is the fundament of
 * various DatabaseHelpers
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
abstract class DatabaseHelper {

	//*****************************************************************//
	//***************** DatabaseHelper class properties ***************//
	//*****************************************************************//
	/**
	 * @var array list of settings
	 */
	protected $settings = array();


	//*****************************************************************//
	//****************** DatabaseHelper class methods **************//
	//*****************************************************************//
	/**
	 * Set value of a column.
	 *
	 * @uses DatabaseHelper::$settings
	 * @param string $column database (table) column
	 * @param mixed $setting
	 * @return boolean true
	 */
	public function set($column, $setting){
		$this->settings[$column] = $setting;
		return true;
	}

	/**
	 * Get value of a column.
	 *
	 * @uses DatabaseHelper::$settings
	 * @param string $column database (table) column
	 * @return mixed if the value is set, else it will return null
	 */
	public function get($column){
		if(isset($this->settings[$column])){
			return $this->settings[$column];
		} else {
			return null;
		}
	}
}


//*****************************************************************//
//******************* DatabaseListHelper class ********************//
//*****************************************************************//
/**
 * DatabaseListHelper class.
 *
 * The DatabaseListHelper class is the fundament of
 * various DatabaseListHelpers
 *
 * @package corelib
 * @subpackage Database
 */
abstract class DatabaseListHelper extends DatabaseHelper {


	//*****************************************************************//
	//************** DatabaseListHelper class methods *****************//
	//*****************************************************************//
	/**
	 * Count number settings.
	 *
	 * @return integer number of settings
	 */
	public function count(){
		return sizeof($this->settings);
	}
}


//*****************************************************************//
//***************** DatabaseListHelperOrder class *****************//
//*****************************************************************//
/**
 * DatabaseListHelperOrder class.
 *
 * The DatabaseListHelperOrder class manages the
 * sorting should be applied on select queries
 *
 * @package corelib
 * @subpackage Database
 */
class DatabaseListHelperOrder extends DatabaseListHelper {


	//*****************************************************************//
	//************ DatabaseListHelperOrder class methods **************//
	//*****************************************************************//
	/**
	 * Set sort order for column.
	 *
	 * @see DatabaseHelper::set()
	 */
	public function set($column, $setting=DATABASE_ORDER_DESC){
		return parent::set($column, $setting);
	}

	/**
	 * Get sort order for column.
	 *
	 * @return string column with sort order, if no sort isset return false
	 */
	public function get($column){
		if($order = parent::get($column)){
			return '`'.$column.'` '.$order;
		} else {
			return false;
		}
	}
}


//*****************************************************************//
//*************** DatabaseListHelperFilter class ******************//
//*****************************************************************//
/**
 * DatabaseListHelperFilter class.
 *
 * The DatabaseListHelperFilter class manages the
 * filters that should be applied on select queries
 *
 * @package corelib
 * @subpackage Database
 */
class DatabaseListHelperFilter extends DatabaseListHelper {

}


//*****************************************************************//
//******************* DatabaseDataHandler class *******************//
//*****************************************************************//
/**
 * DatabaseDataHandler class.
 *
 * The DatabaseDataHandler class manages the changes
 * made when modififying data
 *
 * @package corelib
 * @subpackage Database
 */
class DatabaseDataHandler extends DatabaseHelper {


	//*****************************************************************//
	//************* DatabaseDataHandler class constants ***************//
	//*****************************************************************//
	/**
	 * Set no history break charecter.
	 *
	 * Since null is used for representing null
	 * in the database another character should
	 * be used to ignore changes if no history values
	 * appear. For this ASCII C0 Cancel is used
	 *
	 * @internal
	 */
	const NO_HISTORY = "\024";


	//*****************************************************************//
	//************ DatabaseDataHandler class properties ***************//
	//*****************************************************************//
	/**
	 * @see DatabaseDataHandler::setSpecialValue()
	 * @var array list of special columns
	 * @internal
	 */
	private $special_values = array();

	/**
	 * @see DatabaseDataHandler::getUpdatedColumns()
	 * @see DatabaseDataHandler::getUpdatedColumnValues()
	 * @var array list of updated columns
	 * @internal
	 */
	private $updated_columns = array();

	/**
	 * @see DatabaseDataHandler::getUpdatedColumns()
	 * @see DatabaseDataHandler::getUpdatedColumnValues()
	 * @see DatabaseDataHandler::addExcludeField()
	 * @var array list of columns which should be excluded
	 * @internal
	 */
	private $special_exclude = array();

	/**
	 * @see DatabaseDataHandler::set()
	 * @see DatabaseDataHandler::getHistoryValue()
	 * @var array of history of updated values
	 * @internal
	 */
	private $history_values = array();


	//*****************************************************************//
	//*************** DatabaseDataHandler class methods ***************//
	//*****************************************************************//
	/**
	 * Set column value.
	 *
	 * @uses DatabaseDataHandler::$updated_columns
	 * @uses DatabaseDataHandler::$history_values
	 * @param string $column column
	 * @param mixed $setting value of column
	 * @param mixed $history value of the column before the change
	 * @return boolean true
	 */
	public function set($column, $setting, $history=self::NO_HISTORY){
		$this->updated_columns[$column] = $column;
		if($history !== self::NO_HISTORY){
			$this->history_values[$column] = $history;
		}
		return parent::set($column, $setting);
	}

	/**
	 * Get historic value of column.
	 *
	 * @uses DatabaseDataHandler::$history_values
	 * @param string column
	 * @return mixed historic value of column, if none available then return false;
	 */
	public function getHistoryValue($column){
		if(array_key_exists($column, $this->history_values)){
			return $this->history_values[$column];
		} else {
			return self::NO_HISTORY;
		}
	}

	/**
	 * Get updated columns.
	 *
	 * @uses DatabaseHelper::$settings
	 * @uses DatabaseDataHandler::$special_values
	 * @uses DatabaseDataHandler::$special_exclude
	 * @param string $column,...
	 * @return array list of updated columns
	 */
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

	/**
	 * Get updated values.
	 *
	 * @uses DatabaseHelper::$settings
	 * @uses DatabaseDataHandler::$special_exclude
	 * @param string $column,...
	 * @return array list of updated columns
	 */
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

	/**
	 * check to se if there have been any changes made.
	 *
	 *
	 * @param string $column if column is specified the check is only made on that column
	 * @return boolean true if changed else return false
	 */
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

	/**
	 * Set special column value.
	 *
	 * @uses DatabaseDataHandler::$special_values
	 * @param string $column
	 * @param mixed $value
	 */
	public function setSpecialValue($column, $value){
		$this->special_values[$column] = $value;
		return true;
	}

	/**
	 * Add a exclude filter.
	 *
	 * Prevent a column from being retrieved when using
	 * {@link DatabaseDataHandler::getUpdatedColumnValues()} and
	 * {@link DatabaseDataHandler::getUpdatedColumns()}.
	 *
	 * @uses DatabaseDataHandler::$special_exclude
	 * @param string $column
	 * @return boolean true
	 */
	public function addExcludeField($column){
		$this->special_exclude[$column] = $column;
		return true;
	}

	/**
	 * Remove a exclude filter.
	 *
	 * @uses DatabaseDataHandler::$special_exclude
	 * @see DatabaseDataHandler::addExcludeField
	 * @param string $column
	 * @return boolean true
	 */
	public function removeExcludeField($column){
		if(isset($this->special_exclude[$column])){
			unset($this->special_exclude[$column]);
			return true;
		} else {
			return false;
		}
	}
}
?>