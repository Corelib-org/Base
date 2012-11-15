<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * ORM Relations helper
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
 * @subpackage ORM
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//******************* ORMRelationHelper class *********************//
//*****************************************************************//
/**
 * ORM Relationship helper class.
 *
 * This object is not design to be accessed manually, the purpose of
 * this class is only for internal program logic while handling orm
 * class relationships.
 *
 * @category corelib
 * @package Base
 * @subpackage ORM
 * @internal
 */
class ORMRelationHelper {


	//*****************************************************************//
	//************* ORMRelationHelper class properties ****************//
	//*****************************************************************//
	/**
	 * Object relations reference array.
	 *
	 * @var array list of object and actions.
	 */
	private $relations = array();

	/**
	 * Reference class name.
	 *
	 * @var string class name.
	 */
	private $reference_class = null;

	//*****************************************************************//
	//*************** ORMRelationHelper class constants ***************//
	//*****************************************************************//
	/**
	 * Create action status constant.
	 *
	 * @internal
	 * @var integer
	 */
	const CREATE = 1;

	/**
	 * Delete action status constant.
	 *
	 * @internal
	 * @var integer
	 */
	const REMOVE = 0;


	//*****************************************************************//
	//*************** ORMRelationHelper class methods *****************//
	//*****************************************************************//
	/**
	 * ORM Relation helper constructor.
	 *
	 * @param string $reference reference class name
	 * @return void
	 * @uses ORMRelationHelper::$reference_class
	 */
	public function __construct($reference){
		$this->reference_class = $reference;
	}

	/**
	 * Add new object relation.
	 *
	 * @param object $obj instance of object having a getID method
	 * @param object $reference optional instance of {@link ORMRelationHelper::$reference_class}
	 * @return object instance of {@link ORMRelationHelper::$reference_class}
	 * @uses ORMRelationHelper::_registerAction()
	 * @uses ORMRelationHelper::$reference_class
	 * @uses ORMRelationHelper::CREATE
	 */
	public function add($obj, $reference=null){
		if(is_null($reference)){
			$reference = new $this->reference_class();
		}
		$this->_registerAction($obj, $reference, self::CREATE);
		return $reference;
	}

	/**
	 * Remove new object relation.
	 *
	 * @param object $obj instance of object having a getID method
	 * @return boolean true on success, else return false
	 * @uses ORMRelationHelper::_registerAction()
	 * @uses ORMRelationHelper::$reference_class
	 * @uses ORMRelationHelper::REMOVE
	 */
	public function remove($obj){
		$this->_registerAction($obj, new $this->reference_class(), self::REMOVE);
		return true;
	}

	/**
	 * Iterate over each relation.
	 *
	 * @uses ORMRelationHelper::$relations
	 * @return mixed array(relation_object, link) or false on error
	 */
	public function each(){
		if(count($this->relations) > 0){
			if(list(,$relation) = each($this->relations)){
				return array($relation[0], $relation[1]);
			} else {
				reset($this->relations);
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Commit relationship change.
	 *
	 * @param object $obj instance of object returned by {@link ORMRelationHelper::each()}
	 * @param object $link instance of {@link ORMRelationHelper::$reference_class} returned by {@link ORMRelationHelper::each()}
	 * @uses ORMRelationHelper::$relations
	 * @uses ORMRelationHelper::$reference_class
	 * @uses ORMRelationHelper::CREATE
	 * @uses ORMRelationHelper::REMOVE
	 * @uses ORMRelationHelper::_validate()
	 */
	public function commit($obj, $link){
		if($link instanceof $this->reference_class){
			$this->_validate($obj);
			if($this->relations[$obj->getID()][2] == ORMRelationHelper::CREATE){
				$link->commit();
			} else {
				$link->delete();
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Register new relation action.
	 *
	 * Validate and register a relation change
	 *
	 * @param object $obj instance of object having a getID method
	 * @param object $reference instance of {@link ORMRelationHelper::$reference_class}
	 * @param integer $action
	 * @return boolean true on succes, else return false
	 * @uses ORMRelationHelper::$relations
	 * @uses ORMRelationHelper::_validate()
	 */
	private function _registerAction($obj, $reference, $action){
		$this->_validate($obj);
		$this->relations[$obj->getID()] = array($obj, $reference, $action);
	}

	/**
	 * Validate relation object.
	 *
	 * @param object $obj instance of object having a getID method
	 * @return boolean true on success, else return false
	 * @throws BaseException if object is invalid
	 */
	private function _validate($obj){
		if(!is_callable(array($obj, 'getID'))){
			throw new BaseException('Relation object is invalid, getID method is missing', E_USER_ERROR);
		} else if(is_null($obj->getID())){
			throw new BaseException('Relation object is invalid, getID returned null', E_USER_ERROR);
		} else {
			return true;
		}
	}
}
?>