<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * MySQLi DAO database tool class
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
 * @subpackage Manager
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 2.0.0 ($Id: ErrorHandler.php 5143 2010-02-16 12:41:35Z wayland $)
 */

//*****************************************************************//
//****************** MySQLi_DatabaseTool class ********************//
//*****************************************************************//
/**
 * MySQLi_DatabaseTool class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 * @see DAO_DatabaseTool
 */
class MySQLi_DatabaseTool extends DatabaseDAO implements Singleton,DAO_DatabaseTool {


	//*****************************************************************//
	//************ MySQLi_DatabaseTool class properties ***************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var MySQLi_DatabaseTool
	 * @internal
	 */
	private static $instance = null;


	//*****************************************************************//
	//************** MySQLi_DatabaseTool class methods ****************//
	//*****************************************************************//
	/**
	 * 	Return instance of MySQLi_DatabaseTool.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses MySQLi_DatabaseTool::$instance
	 *	@return MySQLi_DatabaseTool
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new MySQLi_DatabaseTool();
		}
		return self::$instance;
	}

	/**
	 * Get table status from all tables.
	 *
	 * @see DAO_DatabaseTool::getObjectsAndRevisions()
	 */
	public function getObjectsAndRevisions(){
		$query = 'SHOW TABLE STATUS';
		$query = $this->masterQuery(new MySQLiQuery($query));
		$status = array();
		while ($res = $query->fetchArray()) {
			if(preg_match('/Revision:\s+([0-9]+)/', $res['Comment'], $matches)){
				$status[$res['Name']] = $matches[1];
			}
		}
		return $status;
	}

	/**
	 * Get table dependencies.
	 *
	 * @see DAO_DatabaseTool::getObjectsDependencies()
	 */
	public function getObjectsDependencies($data){
		$dependencies = array();
		if(preg_match_all('/REFERENCES\s.*?(\w+).*?\s\(/msi', $data, $matches)){
			$dependencies = array_merge($dependencies, $matches[1]);
		}
		return $dependencies;
	}

	/**
	 * Update database.
	 *
	 * @see DAO_DatabaseTool::performUpdate()
	 */
	public function performUpdate($data){
		foreach (preg_split('/;\s*$/m', $data) as $query){
			if(!empty($query)){
				$this->masterQuery(new MySQLiQuery($query));
			}
		}
	}
}
?>