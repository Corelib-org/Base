<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package AutoGenerated
 * @subpackage ${classname}
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 * @filesource
 */

/**
 * ${classname} MySQLi DAO Class
 * 
 * @package AutoGenerated
 */
class MySQLi_${classname} extends DatabaseDAO implements Singleton,DAO_${classname} {
	/**
	 * @var MySQLi_${classname}
	 */
	private static $instance = null;
	
	/**
	 * @ignore
	 */
	const SELECT_COLUMNS = '${selectcolumns}';
	
	/**
	 * @return MySQLi_${classname}
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_${classname}();
		}
		return self::$instance;	
	}
	

	//*****************************************************************//
	//************************ Utility methods ************************//
	//*****************************************************************//
	/* Utility methods */
	/* Utility methods end */	
	

	//*****************************************************************//
	//******************* Data modification methods *******************//
	//*****************************************************************//	
	/**
	 * @see DAO_${classname}::create()
	 */
	public function create(DatabaseDataHandler $data){
		/* Special create fields */
		/* Special create fields end */
				
		$columns = $data->getUpdatedColumns();
		$values = $data->getUpdatedColumnValues();	
		
		$query = MySQLiTools::makeInsertStatement('${tablename}', $columns);
		$query = $this->masterQuery(new MySQLiQueryStatement($query, $values));
			
		if($id = (int) $query->getInsertID()){
			/* After create actions */
			/* After create actions end */
			return $id;
		} else {
			return false;	 
		}
	}
	
	/**
	 * @see DAO_${classname}::update()
	 */
	public function update($id, DatabaseDataHandler $data){
		/* Special update fields */
		/* Special update fields end */
		
		$columns = $data->getUpdatedColumns();
		$values = $data->getUpdatedColumnValues();			
		
		$query = MySQLiTools::makeUpdateStatement('${tablename}', $columns, 'WHERE `'.${classname}::FIELD_ID.'`=?');

		$query = $this->masterQuery(new MySQLiQueryStatement($query, $values, $id));

		/* After edit actions */
		/* After edit actions end */
		
		if($query->getAffectedRows() > 0){
			return true;
		} else {
			return false;	 
		}
	}

	/**
	 * @see DAO_${classname}::read()
	 */
	public function read($id){
		$query = 'SELECT '.self::SELECT_COLUMNS.'
		          FROM `${tablename}`
		          WHERE `'.${classname}::FIELD_ID.'`=\''.$id.'\'';
		$query = $this->slaveQuery(new MySQLiQuery($query));
		
		return $query->fetchArray();
	}
	
	/**
	 * @see DAO_${classname}::delete()
	 */
	public function delete($id){
		/* Delete actions */
		/* Delete actions end */		
		
		$query = 'DELETE FROM `${tablename}`
		          WHERE `'.${classname}::FIELD_ID.'`=\''.$id.'\'';
		$query = $this->masterQuery(new MySQLiQuery($query));
			
		if($query->getAffectedRows() > 0){
			return true;
		} else {
			return false;	 
		}
	}
}
?>