<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * MYSQLi class for View caching.
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Views
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 * @since Version 5.0
 */

//*****************************************************************//
//*********************** MySQLi_View class ***********************//
//*****************************************************************//
/**
 * View MySQLi DAO class
 *
 * @category corelib
 * @package Base
 * @subpackage Views
 * @internal
 */
class MySQLi_View extends DatabaseDAO implements Singleton,DAO_View {


	//*****************************************************************//
	//**************** MySQLi_View class properties *******************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var Base
	 * @internal
	 */
	private static $instance = null;

	/**
	 * 	Return instance of MySQLi_View.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses MySQLi_View::$instance
	 *	@return MySQLi_View
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_View();
		}
		return self::$instance;
	}

	/**
	 * Read view cache.
	 *
	 * @see DAO_View::read()
	 */
	public function read(DatabaseViewHelper $helper){
		$keys = $helper->getKeyNames();
		$values = $helper->getKeyValues();

		$columns = array();
		foreach($keys as $id => $key){
			$columns[] = '`'.View::KEY_PREFIX.$key.'`=\''.$this->escapeString($values[$id]).'\'';
		}

		$query = 'SELECT `'.View::XML_COLUMN.'`, `'.View::OBJECT_COLUMN.'`
		          FROM `'.$helper->getTable().'`
		          WHERE '.implode(' AND ', $columns).'';
		$query = new MySQLiQuery($query);

		try {
			$this->masterQuery($query);
		} catch(BaseException $error) {
			if($query->getErrno() == 1146){
				var_dump($helper);
				exit;
				$this->_createTable($helper);
				return $this->read($helper);
			} else {
				trigger_error('Unknown error in view.', E_USER_ERROR);
			}
		}
		return $query->fetchArray();
	}

	/**
	 * Update view cache.
	 *
	 * @see DAO_View::update()
	 */
	public function update(DatabaseViewHelper $helper, $xml, $object){
		$keys = $helper->getKeyNames();
		$values = $helper->getKeyValues();

		$columns = array();
		$column_values = array();
		foreach($keys as $id => $key){
			$columns[] = '`'.View::KEY_PREFIX.$key.'`';
			$column_values[] = '\''.$this->escapeString($values[$id]).'\'';
		}

		$query = 'REPLACE INTO `'.$helper->getTable().'` ('.implode(', ', $columns).', `'.View::XML_COLUMN.'`, `'.View::OBJECT_COLUMN.'`)
		          VALUES('.implode(', ', $column_values).', \''.$this->escapeString($xml).'\', \''.$this->escapeString($object).'\')';
		$query = new MySQLiQuery($query);

		try {
			$this->masterQuery($query);
		} catch(BaseException $error) {
			if($query->getErrno() == 1146){
				$this->_createTable($helper);
				$this->update($helper, $xml, $object);
			} else {
				trigger_error('Unknown error in view.', E_USER_ERROR);
				return false;
			}
		}
		return true;
	}

	/**
	 * lear view cache object.
	 *
	 * @see DAO_View::clean()
	 */
	public function clean(DatabaseViewHelper $helper, $object){
		$keys = $helper->getKeyNames();
		$callbacks = $helper->getKeyCallbacks();
		foreach($keys as $id => $key){
			$columns[] = '`'.View::KEY_PREFIX.$key.'`=\''.$this->escapeString($object->$callbacks[$id]()).'\'';
		}
		$query = 'DELETE FROM `'.$helper->getTable().'`
		          WHERE '.implode(' AND ', $columns);
		$query = new MySQLiQuery($query);
		try {
			$this->masterQuery($query);
		} catch(BaseException $error) {
			if($query->getErrno() == 1146){
				$this->_createTable($helper);
			} else {
				trigger_error('Unknown error in view.', E_USER_ERROR);
				return false;
			}
		}
		return true;
	}

	/**
	 * Get database join statement.
	 *
	 * @see DAO_View::getJoinStatement()
	 */
	public function getJoinStatement(DatabaseViewHelper $helper, $table){
		$keys = $helper->getKeyNames();
		foreach($keys as $key){
			$columns[] = '`'.$key.'`=`'.View::KEY_PREFIX.$key.'`';
		}

		$join = ' LEFT JOIN `'.$helper->getTable().'`
		                 ON '.implode(' AND ', $columns);
		return $join;
	}

	/**
	 * Create cache table in database.
	 *
	 * @param DatabaseViewHelper $helper
	 * @return void
	 */
	private function _createTable(DatabaseViewHelper $helper){
		$keys = $helper->getKeyNames();
		$columns = array();
		foreach($keys as $id => $key){
			$columns[] = '`'.View::KEY_PREFIX.$key.'` VARCHAR(255) NOT NULL';
			$primary[] = '`'.View::KEY_PREFIX.$key.'`';
		}
		$query = 'CREATE TABLE `'.$helper->getTable().'`(
		                        '.implode(', ', $columns).',
		                        `'.View::XML_COLUMN.'` TEXT NOT NULL ,
		                        `'.View::OBJECT_COLUMN.'` TEXT NOT NULL ,
		                         PRIMARY KEY('.implode(', ', $primary).'))
		          ENGINE=InnoDB COMMENT=\'Temporary view cache table\'';
		$this->masterQuery(new MySQLiQuery($query));
	}
}

?>