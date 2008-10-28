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

//*****************************************************************//
//************************* Event Classes *************************//
//*****************************************************************//
/**
 * ${classname} modifed base event
 * 
 * @package AutoGenerated
 */
abstract class ${classname}Modify implements Event {
	/**
	 * @var ${classname}
	 */
	protected $model;
	
	/**
	 * Create event and set modified object instance
	 * 
	 * @param ${classname} $model
	 */
	function __construct(${classname} $model){
		$this->model = $model;
	}

	/**
	 * Get modified object instance
	 * 
	 * @return ${classname}
	 */
	public function getModel(){
		return $this->model;
	}
}

/**
 * Before commit event
 * 
 * This event i triggered each time {@link ${classname}::commit()} is called
 * but before any changes are made to the database
 * 
 * @package AutoGenerated
 */
class ${classname}ModifyBeforeCommit extends ${classname}Modify { }

/**
 * Before delete event
 * 
 * This event i triggered each time {@link ${classname}::delete()} is called
 * but before any changes are made to the database
 * 
 * @package AutoGenerated
 */
class ${classname}ModifyBeforeDelete extends ${classname}Modify { }

/**
 * After commit event
 * 
 * This event i triggered each time {@link ${classname}::commit()} is called
 * but after any changes have been made to the database
 * 
 * @package AutoGenerated
 */
class ${classname}ModifyAfterCommit extends ${classname}Modify { }

/**
 * After delete event
 * 
 * This event i triggered each time {@link ${classname}::delete()} is called
 * but after any changes have been made to the database
 * 
 * @package AutoGenerated
 */
class ${classname}ModifyAfterDelete extends ${classname}Modify { }


//*****************************************************************//
//************************* DAO Interface *************************//
//*****************************************************************//
/**
 * DAO interface for ${classname}
 * 
 * @package AutoGenerated
 */ 
interface DAO_${classname} {
	/**
	 * Save object data in database
	 * 
	 * @param DatabaseDataHandler $data
 	 * @return integer id on success, else return false
 	 */
	public function create(DatabaseDataHandler $data);
	/**
	 * Update object data in database
	 * 
	 * @param integer database reference ID 
	 * @param DatabaseDataHandler $data
	 * @return boolean true on success, else return false
	 */	
	public function update($id, DatabaseDataHandler $data);
	/**
	 * Get object data from database
	 *
	 * @param integer database reference ID  
	 * @return array on success, else return false
	 */
	public function read($id);
	/**
	 * Remove data from database
	 * 
	 * @param integer database reference ID
	 * @return boolean true on success, else return false
	 */
	public function delete($id);
}

//*****************************************************************//
//******************** Abstract View classes **********************//
//*****************************************************************//
/**
 * Simple class for handling database cached xml views
 * 
 * @package AutoGenerated
 */
abstract class ${classname}View extends View { }

/**
 * Simple class for listing database cached xml views
 * 
 * @package AutoGenerated
 */
abstract class ${classname}ViewList implements ViewList { }


//*****************************************************************//
//************************** Model class **************************//
//*****************************************************************//
/**
 * ${classname} model
 * 
 * @package AutoGenerated
 */
class ${classname} implements Output {
	/* Properties */
	private $id = null;
	/* Properties end */
	
	/* Converter properties */
	/* Converter properties end */	
	
	/* Field constants */
	/* Field constants end */

	/* Enum constants */
	/* Enum constants end */
	
	private $dao = null;
	
	/**
	 * @var DatabaseDataHandler
	 */
	private $datahandler = null;
	
	const DAO = '${classname}';
	
	/**
	 * Create model ${classname} instance
	 * 
	 * @param integer $id object id
	 * @param array $array object data from another data source
	 */
	public function __construct($id = null, $array = array()){
		$this->id = $id;
		if(sizeof($array) > 0){
			$this->_setFromArray($array);
		}
		$this->datahandler = new DatabaseDataHandler();
	}
	
	//*****************************************************************//
	//*************************** Get methods *************************//
	//*****************************************************************//
	/* Getter methods */
	/* Getter methods end */
	
	//*****************************************************************//
	//*************************** Set methods *************************//
	//*****************************************************************//	
	/* Setter methods */
	/* Setter methods end */
	
	
	//*****************************************************************//
	//********************* Converter set methods *********************//
	//*****************************************************************//	
	/* Converter methods */
	/* Converter methods end */

	
	//*****************************************************************//
	//************************ Utility methods ************************//
	//*****************************************************************//	
	/* Utility methods */
	/* Utility methods end */

	
	//*****************************************************************//
	//********************** Data change methods **********************//
	//*****************************************************************//
	/**
	 * Delete data from database
	 * 
	 * @return boolean return true on success, else return false
	 */
	public function delete(){
		return $this->dao->delete($this->id);
	}
	
	/**
	 * Read data from database
	 * 
	 * @return boolean return true on success, else return false
	 */
	public function read(){
		$this->_getDAO(false);
		if($array = $this->dao->read($this->id)){
			$this->_setFromArray($array);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Commit changes to database
	 * 
	 * @return boolean return true on success, else return false
	 */
	public function commit(){
		$event = EventHandler::getInstance();
		$this->_getDAO();
		$event->triggerEvent(new ${classname}ModifyBeforeCommit($this));
		if(is_null($this->id)){
			$r = $this->_create();
		} else {
			$r = $this->_update();
		}
		if($r !== false){
			$event->triggerEvent(new ${classname}ModifyAfterCommit($this));
		}
		return $r;
	}


	//*****************************************************************//
	//************************* Output methods ************************//
	//*****************************************************************//
	/**
	 * @see Output::getXML()
	 * @param DOMDocument $xml
	 * @return DOMElement XML output
	 */
	public function getXML(DOMDocument $xml){
		$${classvar} = $xml->createElement('${classvar}'); 
		/* Get XML method */
		/* Get XML method end */
		return $${classvar};
	}

	//*****************************************************************//
	//************************ Private methods ************************//
	//*****************************************************************//
	/**
	 * Get Current DAO object instance
	 * 
	 * @param boolean $read if true, then read data from database
	 * @return boolean true
	 */	
	protected function _getDAO($read=true){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO(self::DAO);
			if($read){
				$this->read();
			}
		}
		return true;
	}
	/**
	 * Create object in database
	 * 
	 * @return boolean true on success, else return false
	 */
	protected function _create(){
		if($this->id = $this->dao->create($this->datahandler)){
			$this->read();
			return true;
		} else {
			return false;
		}		
	}
	/**
	 * Update object in database
	 * 
	 * @return boolean true on success, else return false
	 */
	protected function _update(){
		if($this->dao->update($this->id, $this->datahandler)){
			$this->read();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Populate model using a arrays as data source
	 * 
	 * @param array $data Data
	 */
	protected function _setFromArray($array){
		/* setFromArray method content */
		if(isset($array[self::FIELD_ID])){
			$this->id = (int) $array[self::FIELD_ID];
		}
		/* setFromArray method content end */
	}
	
}
?>