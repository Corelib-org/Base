<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * View caching.
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
use \Corelib\Base\Converters\Converter, Corelib\Base\PageFactory\Output;
use Corelib\Base\Event\Action as EventAction, Corelib\Base\Event\Event as Event;

//*****************************************************************//
//*********************** DAO_View Interface **********************//
//*****************************************************************//
/**
 * View DAO interface
 *
 * Impliment this interface to add support for
 * other database enginess.
 *
 * @category corelib
 * @package Base
 * @subpackage Views
 */
interface DAO_View {


	//*****************************************************************//
	//****************** DAO_View Interface methods *******************//
	//*****************************************************************//
	/**
	 * Read view cache.
	 *
	 * @param DatabaseViewHelper $helper
	 * @return array view cache data
	 */
	public function read(DatabaseViewHelper $helper);

	/**
	 * Update view cache.
	 *
	 * @param DatabaseViewHelper $helper
	 * @param string $xml view cache xml
	 * @param string $object serialized objects
	 * @return boolean true on success, else return false
	 */
	public function update(DatabaseViewHelper $helper, $xml, $object);

	/**
	 * Clear view cache object.
	 *
	 * @param DatabaseViewHelper $helper
	 * @param Object $object
	 * @return boolean true on success, else return false
	 */
	public function clean(DatabaseViewHelper $helper, $object);

	/**
	 * Get database join statement.
	 *
	 * Get the join statement which should be used when creating lists
	 * based on a view.
	 *
	 * @param DatabaseViewHelper $helper
	 * @param string $table table name on which data should be joined on
	 * @return string join statement
	 */
	public function getJoinStatement(DatabaseViewHelper $helper, $table);
}


//*****************************************************************//
//************************** View Class ***************************//
//*****************************************************************//
/**
 * View class
 *
 * Extend this class when creating views.
 *
 * @category corelib
 * @package Base
 * @subpackage Views
 */
abstract class View extends EventAction implements Output {


	//*****************************************************************//
	//******************** View Class Properties **********************//
	//*****************************************************************//
	/**
	 * @var string xml string
	 * @internal
	 */
	private $xml = null;

	/**
	 * @var DAO_View
	 * @internal
	 */
	private $dao = null;

	/**
	 * Identification keys.
	 *
	 * @var array
	 * @internal
	 */
	private $keys = array();

	/**
	 * Key value get callbacks.
	 *
	 * @var array
	 * @internal
	 */
	private $callbacks = array();

	/**
	 * @var DatabaseViewHelper
	 * @internal
	 */
	private $helper = null;


	//*****************************************************************//
	//********************* View Class Constants **********************//
	//*****************************************************************//
	/**
	 * DAO Class name reference.
	 *
	 * @internal
	 */
	const DAO = 'View';

	/**
	 * Column key prefix.
	 *
	 * @var string prefix
	 * @internal
	 */
	const KEY_PREFIX = 'key_';

	/**
	 * Cached XML data column name.
	 *
	 * @var string
	 * @internal
	 */
	const XML_COLUMN = 'cached_xml_data';

	/**
	 * Cached object data column name.
	 *
	 * @var string
	 * @internal
	 */
	const OBJECT_COLUMN = 'cached_object_data';


	//*****************************************************************//
	//**************** View Class abstract methods ********************//
	//*****************************************************************//
	/**
	 * Generate view.
	 *
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	abstract protected function generate(DOMDocument $xml);

	/**
	 * Get object properties.
	 *
	 * Get a list of properties to be cached. this method
	 * may be overwritten if needed.
	 *
	 * @return array
	 * @see View::_setProperties()
	 */
	protected function _getProperties(){ }

	/**
	 * Set cached properties.
	 *
	 * Set properties from cached property list.
	 * The array used as parameters is the same as
	 * the one return by {@link View::_getProperties()}.
	 * This method may be overwritten if needed.
	 *
	 * @param array $properties
	 * @return void
	 * @see View::_getProperties()
	 */
	protected function _setProperties(array $properties){ }


	//*****************************************************************//
	//********************* View Class abstract ***********************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param array $array
	 * @return void
	 */
	public function __construct($array = array()){
		$this->_setFromArray($array);
	}

	/**
	 * Add object identification key.
	 *
	 * @param string $key key name
	 * @param string $value key value
	 * @return boolean true on success, else return false
	 */
	public function addKey($key, $callback, $value){
		$this->keys[$key] = $value;
		$this->callbacks[$key] = $callback;
		return true;
	}

	/**
	 * Get key value.
	 *
	 * @param string $key key name
	 * @return string key value, else return false
	 */
	public function getKey($key){
		if(isset($this->keys[$key])){
			return $this->keys[$key];
		} else {
			return false;
		}
	}

	/**
	 * Get list helper.
	 *
	 * @return DatabaseViewHelper
	 * @internal
	 */
	public function getListHelper(){
		if(is_null($this->helper)){
			$this->helper = new DatabaseViewHelper('viewcache_'.get_class($this), $this->keys, $this->callbacks);
		}
		return $this->helper;
	}

	/**
	 * Get View XML.
	 *
	 * Get view xml from cache
	 *
	 * @param DOMDocument $xml
	 * @return DOMElement
	 * @internal
	 */
	public function getViewXML(DOMDocument $xml){
		$class = get_class($this);
		$class = new $class();
		$args = func_get_args();
		array_shift($args);
		call_user_func_array(array($class, '__construct'), $args);
		$this->_setViewSettings($class);
		return $class->getXML($xml);
	}

	/**
	 * Clean object cache data.
	 *
	 * @see EventAction::update()
	 * @internal
	 */
	public function update(Event $event){
		$this->_getDAO();
		$this->dao->clean($this->getListHelper(), $event->getModel());
	}

	/**
	 * Get Cached XML content.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 * @internal
	 */
	final public function getXML(DOMDocument $xml){
		if(is_null($this->xml) && !$this->_read()){
			$this->_generate();
		}
		$dom = $this->_createDOMDocument();
		$dom->loadXML($this->xml);
		return $xml->importNode($dom->documentElement, true);
	}

	/**
	 * Generate XML.
	 *
	 * @return void
	 * @internal
	 */
	private function _generate(){
		$dom = $this->_createDOMDocument();
		$dom->appendChild($this->generate($dom));
		$this->xml = $dom->saveXML();
		$this->_getDAO();
		$this->_update();
	 }

	 /**
	  * Get cached data from database.
	  *
	  * @return boolean true on success, else return false
	  * @internal
	  */
	private function _read(){
		$this->_getDAO();
		if($out = $this->dao->read($this->getListHelper())){
			$this->_setFromArray($out);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update data i database.
	 *
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _update(){
		$this->_getDAO();
		$this->dao->update($this->getListHelper(), $this->xml, serialize($this->_getProperties()));
		return true;
	}

	/**
	 * Create DOMDocument.
	 *
	 * @return PageFactoryDOMDocument
	 * @internal
	 */
	private function _createDOMDocument(){
		return new PageFactoryDOMDocument('1.0', 'UTF-8');
	}

	/**
	 * Populate model using an array as data source.
	 *
	 * @param array $data Data
	 * @internal
	 */
	protected function _setFromArray($array){
		if(isset($array['cached_xml_data'])){
			$this->xml = (string) $array['cached_xml_data'];
		}
		if(isset($array['cached_object_data'])){
			$data = unserialize($array['cached_object_data']);
			if(!is_null($data)){
				$this->_setProperties($data);
			}
		}
	}

	/**
	 * Set view settings.
	 *
	 * If a view has special settings this method is called
	 * to set settings on new xml views. implement it to set
	 * your own settings.
	 *
	 * @param View $view
	 * @return void
	 */
	protected function _setViewSettings(View $view){ }

	/**
	 * Get Current DAO object instance.
	 *
	 * @param boolean $read if true, then read data from database
	 * @return boolean true
	 * @internal
	 */
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO(self::DAO);
		}
		return true;
	}
}


//*****************************************************************//
//******************** DatabaseViewHelper Class *******************//
//*****************************************************************//
/**
 * DatabaseViewHelper class.
 *
 * @category corelib
 * @package Base
 * @subpackage Views
 */
class DatabaseViewHelper extends DatabaseListHelper {


	//*****************************************************************//
	//************** DatabaseViewHelper Class properties **************//
	//*****************************************************************//
	/**
	 * @var string table name.
	 * @internal
	 */
	private $table = null;

	/**
	 * @var array column keys
	 * @internal
	 */
	private $keys = array();

	/**
	 * @var array column key callbacks
	 * @internal
	 */
	private $callbacks = array();

	/**
	 * @var DAO_View
	 * @internal
	 */
	private $dao = null;


	//*****************************************************************//
	//*************** DatabaseViewHelper Class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $table cache table name
	 * @param array $keys column keys
	 * @return void
	 * @internal
	 */
	public function __construct($table, array $keys, array $callbacks){
		$this->keys = $keys;
		$this->callbacks = $callbacks;
		$this->table = $table;
	}

	/**
	 * Get cache table name.
	 *
	 * @return string table name
	 * @internal
	 */
	public function getTable(){
		return $this->table;
	}

	/**
	 * Get key names.
	 *
	 * @return array list of keys.
	 * @internal
	 */
	public function getKeyNames(){
		return array_keys($this->keys);
	}

	/**
	 * Get key callbacks.
	 *
	 * @return array list of key callbacks.
	 * @internal
	 */
	public function getKeyCallbacks(){
		return array_values($this->callbacks);
	}

	/**
	 * Get key values.
	 *
	 * @return array key values
	 * @internal
	 */
	public function getKeyValues(){
		return array_values($this->keys);
	}

	/**
	 * Get Join statement.
	 *
	 * @param string $table table name
	 * @return string join statement
	 */
	public function getJoinStatement($table){
		$this->_getDAO();
		return $this->dao->getJoinStatement($this, $table);
	}

	/**
	 * Get Current DAO object instance.
	 *
	 * @param boolean $read if true, then read data from database
	 * @return boolean true
	 * @internal
	 */
	private function _getDAO(){
		if(is_null($this->dao)){
			$this->dao = Database::getDAO(View::DAO);
		}
		return true;
	}
}
?>