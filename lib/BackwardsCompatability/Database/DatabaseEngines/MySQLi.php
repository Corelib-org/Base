<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database MySQLi Connector.
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
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */





//*****************************************************************//
//********************** MySQLiTools class ************************//
//*****************************************************************//
/**
 * MySQLi Tools.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class MySQLiTools {


	//*****************************************************************//
	//******************* MySQLiTools class methods *******************//
	//*****************************************************************//
	/**
	 * Splice any number of fields together.
	 *
	 * Combine each paramater into one array of all fields.
	 * Any number of parameters can be supplied to this method.
	 * either a string with a field name or a array with multiple
	 * field names.
	 *
	 * @param mixed $field string or array with field names.
	 * @return array
	 */
	static public function spliceFields($field=null /*, [$field..] */){
		$fields = func_get_args();
		foreach ($fields as $val) {
			if(is_array($val)){
				foreach ($val as $subval){
					$freturn[] = $subval;
				}
			} else {
				$freturn[] = $val;
			}
		}
		return $freturn;
	}

	/**
	 * Parse NULL value.
	 *
	 * Parse php datatype "null" into a valid mysql statement.
	 * if value is not null the correct format will also be returned.
	 *
	 * @param mixed $value data
	 * @return string
	 */
	static public function parseNullValue($value){
		if(is_null($value)){
			$value = 'NULL';
		} else {
			$value = '\''.$value.'\'';
		}
		return $value;
	}

	/**
	 * Parse bolean value
	 *
	 * Parse PHP datatype "boolean" into valid mysql statement, and
	 * return a string to be used with the enum boolean principle used
	 * by the {@link CodeGenerator}.
	 *
	 * @param string $value
	 * @param boolean $escape if true escape string, else do nothing.
	 * @return string
	 */
	static public function parseBooleanValue($value, $escape=true){
		if($escape){
			if($value === true){
				$value = '\'TRUE\'';
			} else {
				$value = '\'FALSE\'';
			}
		} else {
			if($value === true){
				$value = 'TRUE';
			} else {
				$value = 'FALSE';
			}
		}
		return $value;
	}

	/**
	 * Replace * wildcards with mysql % wildcard.
	 *
	 * @param string $value
	 * @return string
	 */
	static public function parseWildcards($value){
		return str_replace('*', '%', $value);
	}

	/**
	 * Parse unix timestamp into mysql "unix timestamp"
	 *
	 * Convert timestamp to a valid mysql unix timestamp statement
	 * using mysql's {@link FROM_UNIXTIME http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_from-unixtime} function
	 *
	 * @param integer $value unix timestamps
	 * @param boolean $statement if true prepare timestamp for a mysql statement.
	 * @return string
	 */
	static public function parseUnixtimestamp($value,$statement=false){
		if($statement) {
			if(!is_null($value)) {
				return 'FROM_UNIXTIME(?)';
			} else {
				return null;
			}
		} else {
			if(!is_null($value)) {
				return 'FROM_UNIXTIME(\''.$val.'\')';
			} else {
				return 'NULL';
			}
		}
	}

	/**
	 * Prepare order statement
	 *
	 * Convert {@link DatabaseListHelperOrder} into a valid mysql order statement.
	 *
	 * @param DatabaseListHelperOrder $order
	 * @return mixed false if no ordering should be done, else return string with order statement.
	 */
	static public function prepareOrderStatement(DatabaseListHelperOrder $order){
		$args = func_get_args();
		$fields = array();
		array_shift($args);
		while (list(,$val) = each($args)) {
			if($arg = $order->get($val)){
				$fields[] = ''.$arg.'';
			}
		}
		if(sizeof($fields) > 0){
			return implode(', ', $fields);
		} else {
			return false;
		}
	}

	/**
	 * Prepare limit statement.
	 *
	 * Convert limit and offset into a valid mysql statement
	 *
	 * @param integer $offset
	 * @param integer $limit
	 * @return string
	 */
	static public function prepareLimitStatement($offset=null, $limit=null){
		if(!is_null($offset) && !is_null(!$limit)){
			return 'LIMIT '.$offset.', '.$limit;
		} else if(!is_null($offset) && is_null($limit)){
			return 'OFFSET '.$offset;
		} else if(is_null($offset) && !is_null($limit)){
			return 'LIMIT '.$limit;
		} else {
			return false;
		}
	}

	/**
	 * Create prepared statement compatible insert statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $param extra parameters
	 * @return string
	 */
	static public function makeInsertStatement($table, array $fields, $param='', $options=''){
		return 'INSERT '.$options.' INTO `'.$table.'`  '.self::_makeInsertReplaceValues($fields).' '.$param;
	}

	/**
	 * Create prepared statement compatible replace statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @return string
	 */
	static public function makeReplaceStatement($table, array $fields){
		return 'REPLACE INTO `'.$table.'` '.self::_makeInsertReplaceValues($fields);
	}

	/**
	 * Create prepared statement compatible update statement.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param string $where where statement
	 * @return string
	 */
	static public function makeUpdateStatement($table, array $fields, $where=''){
		$query = 'UPDATE `'.$table.'`'."\n".' SET';
		return $query.' '.self::makeUpdateColumns($fields).' '.$where;
	}

	/**
	 * Convert array to valid mysql update column list.
	 *
	 * @param array $fields
	 * @return string
	 */
	static public function makeUpdateColumns(array $fields){
		$qfields = array();
		foreach ($fields as $field => $value){
			if(is_integer($field)){
				$qfields[] = ' `'.$value.'`=?';
			} else {
				$qfields[] = ' `'.$field.'`='.$value.'';
			}
		}
		return implode(', ', $qfields);
	}

	/**
	 * Convert several values into a mysql IN statement.
	 *
	 * @param array $values
	 * @return string
	 */
	static public function makeInStatement(array $values){
		if(sizeof($values)){
			foreach ($values as $key => $val){
				if(!is_numeric($val)){
					$val = '\''.$val.'\'';
				}
				$values[$key] = $val;
			}
			return 'IN('.implode(', ', $values).') ';
		} else {
			return '=FALSE ';
		}
	}

	/**
	 * Create insert/replace values.
	 *
	 * @param array $fields
	 * @return string
	 */
	static protected function _makeInsertReplaceValues(array $fields){
		$qfields = array();
		$qvalues = array();
		foreach ($fields as $field => $value){
			if(is_integer($field)){
				$qfields[] = '`'.$value.'`';
				$qvalues[] = '?';
			} else {
				$qfields[] = '`'.$field.'`';
				$qvalues[] = $value;
			}
		}
		return '('.implode(', ', $qfields).')VALUES('.implode(', ', $qvalues).')';
	}
}
?>