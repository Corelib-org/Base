<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager database tool.
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Interfaces.php 5218 2010-03-16 13:07:41Z wayland $)
 * @internal
 */


//*****************************************************************//
//***************** DAO_DatabaseTool interface ********************//
//*****************************************************************//
/**
 * Database tool DAO interface.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 * @internal
 */
interface DAO_DatabaseTool {


	//*****************************************************************//
	//************* DAO_DatabaseTool interface methods ****************//
	//*****************************************************************//
	/**
	 * Get table revision status from all tables.
	 *
	 * @return array with tables and current revision
	 */
	public function getObjectsAndRevisions();

	/**
	 * Get table dependencies.
	 *
	 * @param string $data create table statement
	 * @return array list of table dependencies
	 */
	public function getObjectsDependencies($data);

	/**
	 * Update database.
	 *
	 * @param string $data queries to run
	 * @return boolean true on success, else return false
	 */
	public function performUpdate($data);
}


//*****************************************************************//
//********************* DatabaseTool class ************************//
//*****************************************************************//
/**
 * Database tool.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 * @internal
 */
class DatabaseTool implements Output {


	//*****************************************************************//
	//**************** DatabaseTool class properties ******************//
	//*****************************************************************//
	/**
	 * @var array objects in database
	 * @internal
	 */
	private $objects = array();

	/**
	 * @var array script updates order.
	 * @internal
	 */
	private $order = array();

	/**
	 * @var array updates to exclude
	 * @internal
	 */
	private $excludes = array();

	/**
	 * @var DAO_DatabaseTool
	 * @internal
	 */
	private $dao = null;


	//*****************************************************************//
	//****************** DatabaseTool class methods *******************//
	//*****************************************************************//
	/**
	 * Set table excludes.
	 *
	 * specify any numbers of parameters of tables to exclude.
	 *
	 * @param string $item
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function setExcludes($item=null /*, [$items...] */){
		$this->excludes = func_get_args();
		return true;
	}

	/**
	 * Write changes to database.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function update(){
		$this->_getDAO();
		$objects = $this->_findDatabaseScripts();
		foreach ($objects as $object => $actions){
			foreach ($actions['actions'] as $object => $action){
				$this->dao->performUpdate($action['action']);
			}
		}
		return true;
	}

	/**
	 * Get content XML.
	 *
	 * Get list of pending updates.
	 *
	 * @see Output::getXML()
	 * @internal
	 */
	public function getXML(DOMDocument $xml){
		$objects = $this->_findDatabaseScripts();
		$updates = $xml->createElement('database');

		foreach ($objects as $object => $actions){
			$objectXML = $updates->appendChild($xml->createElement('object'));
			$objectXML->setAttribute('name', $object);
			if(sizeof($actions['dependencies'])){
				$dependencies = $objectXML->appendChild($xml->createElement('dependencies'));
				foreach ($actions['dependencies'] as $dependency){
					$dependencies->appendChild($xml->createElement('dependency', $dependency));
				}
			}
			$actionsXML = $objectXML->appendChild($xml->createElement('actions'));
			foreach ($actions['actions'] as $object => $action){
				$update = $actionsXML->appendChild($xml->createElement('action', $action['action']));
				$update->setAttribute('type', $action['type']);
			}
		}
		return $updates;
	}

	/**
	 * Find database update scripts.
	 *
	 * @return array list of update actions
	 * @internal
	 */
	private function _findDatabaseScripts(){
		$config = ManagerConfig::getInstance();
		$database = Database::getPrefix();
		$registry = $config->getPropertyXML('database');
		$this->_getDAO();
		foreach ($this->dao->getObjectsAndRevisions() as $object => $revision){
			$this->objects[$object]['current'] = $revision;
		}


		$xpath = new DOMXPath($registry->ownerDocument);
		$xpath = $xpath->query('engine[@id = "'.$database.'"]/scripts', $registry);
		for ($i = 0; $item = $xpath->item($i); $i++){
			$this->_findFiles(trim(Manager::parseConstantTags($item->nodeValue)));
		}

		foreach ($this->objects as $object => $data){
			if(!in_array($object, $this->order)){
				$this->_findDependencies($object);
			}
		}
		$actions = array();
		foreach ($this->order as $object){
			if(isset($this->objects[$object]['actions']) && !in_array($object, $this->excludes)){
				if(!isset($actions[$object])){
					$actions[$object]['actions'] = array();
					$actions[$object]['dependencies'] = array();
				}
				$actions[$object]['actions'] = array_merge($actions[$object]['actions'], $this->objects[$object]['actions']);
				if(isset($this->objects[$object]['dependencies'])){
					$actions[$object]['dependencies'] = array_merge($actions[$object]['dependencies'], $this->objects[$object]['dependencies']);
				}
			}
		}
		return $actions;
	}

	/**
	 * Find update dependencies.
	 *
	 * @param string $object table.
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _findDependencies($object){
		$dependecies = array();

		if(isset($this->objects[$object]['upgrades'])){
			foreach ($this->objects[$object]['upgrades'] as $data){
				$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($data));
				$this->objects[$object]['actions'][] = array('type' => 'update', 'action' => $data);
			}
		}
		if(isset($this->objects[$object]['create'])){
			$dependecies = array_merge($dependecies, $this->dao->getObjectsDependencies($this->objects[$object]['create']['filename']));
			$this->objects[$object]['actions'][] = array('type' => 'create', 'action' => $this->objects[$object]['create']['filename']);
		}
		$resolved = true;

		foreach ($dependecies as $dependecy){
			$this->objects[$object]['dependencies'][] = $dependecy;
			if(!in_array($dependecy, $this->order) && isset($this->objects[$dependecy]) && $object != $dependecy){
				$this->_findDependencies($dependecy);
			} else if(!isset($this->objects[$dependecy])){
				 $resolved = false;
			}
		}

		if($resolved){
			$this->order[] = $object;
		}
		return true;
	}

	/**
	 * Add object update script.
	 *
	 * @param string $filename
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _addObject($filename){
		if(preg_match('/^(.*?)\.(.*?)\./', basename($filename), $matches)){
			$object = trim($matches[1]);
			if(strstr($matches[2], '-')){
				list($revision, $head) = explode('-', $matches[2], 2);
				if(isset($this->objects[$object]['current']) && $head > $this->objects[$object]['current']){
					$this->objects[$object]['upgrades'][$head] = file_get_contents($filename);
				}
			} else if(!isset($this->objects[$object]) || (isset($this->objects[$object]['create']) && $this->objects[$object]['create']['revision'] < $matches[2])){
				$this->objects[$object]['create'] = array('revision' => $matches[2], 'filename' => file_get_contents($filename));
			}
		}
		return true;
	}

	/**
	 * Search for update scripts
	 *
	 * @param string $dir directory to search
	 * @return void
	 * @internal
	 */
	private function _findFiles($dir){
		if(substr($dir, 0, -1) != '/'){
			$dir = $dir.'/';
		}
		if(is_dir($dir) && is_readable($dir)){
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if($entry{0} != '.' && is_file($dir.$entry)){
					$this->_addObject($dir.$entry);
				} else if ($entry{0} != '.' && is_dir($dir.$entry)){
					$this->_findFiles($dir.$entry);
				}
			}
		}
	}

	/**
	 * Get Current DAO object instance.
	 *
	 * @uses DatabaseTool::$dao
	 * @uses Database::getDAO()
	 * @return boolean true
	 * @internal
	 */
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO('DatabaseTool');
		}
		return true;
	}
}
?>