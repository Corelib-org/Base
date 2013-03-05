<?php
namespace Corelib\Base\ObjectRelationalMapping;

use Corelib\Base\PageFactory\Output, Corelib\Base\ServiceLocator\Locator, Corelib\Base\Converters\Converter;

abstract class Object extends ObjectBase implements Output {

	protected $datahandler = null;
	protected $dao = null;
	protected $cache = null;


	private $_properties = array();
	private $_property_raw_mode = false;
	private $_property_force = false;
	private $_created = false;
	/**
	 * @var Metadata\Parser
	 */
	private $_metadata = null;

	const DATA_ACCESS_METADATA_PRIMARY = 'primary';
	const DATA_ACCESS_METADATA_DATAFIELD = 'datafield';

	/**
	 * @param array $array
	 * @param Metadata\Parser $metadata
	 * @uses \Corelib\Base\ServiceLocator\Locator::get()
	 */
	public function __construct(array $array=array(), Metadata\Parser $metadata=null){
		$this->datahandler = new \DatabaseDataHandler();
		if(is_null($metadata)){
			$this->_metadata = new Metadata\Parser($this);
		} else {
			$this->_metadata = $metadata;
			if($this->_metadata->getName() != get_class($this)){
				throw new Exception('Incorrect metadata class: '.$this->_metadata->getName().' != '.get_class($this));
			}
		}

		$this->dao = Locator::get('Corelib\Base\Database\Connection')->getDAO(
			$this->_metadata->getShortName(),
			$this->_metadata->getNamespaceName()
		);

		if(sizeof($array) > 0){
			$this->_setFromArray($array);
		}
	}


	public function commit(){


			/*
			$event = EventHandler::getInstance();
			$this->_getDAO();
			$event->trigger(new SignupModifyBeforeCommit($this));
			if(is_null($this->id)){'
				$r = $this->_create();
			} else {
				$r = $this->_update();
			}
	*/
		/* Relationship commit actions */
		/* Relationship commit actions end */
/*
		if($r !== false){
			$this->_cleanup();
			$event->trigger(new SignupModifyAfterCommit($this));
		}
		return $r;
*/

		$primary = $this->_metadata->searchMetadataProperties(self::DATA_ACCESS_METADATA_PRIMARY, true);
		if(!$this->_created){
		//	print_r($this->datahandler);
			if($updated = $this->dao->create($this->_metadata, $primary, $this->datahandler)){
				$this->_created = true;
				$this->_setFromArray($updated);
				return true;
			}
		} else {
			if($updated = $this->dao->update($this->_metadata, $primary, $this->_getPropertyValues($primary), $this->datahandler)){
				$this->_setFromArray($updated);
				return true;
			}
		}
		return false;
	}

	public function delete(){
		$primary = $this->_metadata->search(self::DATA_ACCESS_METADATA_PRIMARY, true);
		$this->_created = false;
		return $this->dao->delete($primary, $this->_getPropertyValues($primary));
	}

	public function getXML(\DOMDocument $xml){
		$element_name = $this->_convertMethodToProperty(get_class($this), '-');
		if(substr($element_name, 0,1) == '-'){
			$element_name = substr($element_name, 1);
		}
		$e = $xml->createElement($element_name);
		foreach($this->_metadata->getMetadataProperties() as $property){
			$name = $property->getName();
			$attribute = str_replace('_', '-', $name);

			$value = $this->_getProperty($name);
			if(!empty($value)){
				$e->setAttribute($attribute, $value);
			}
		}
		return $e;
	}

	/*
	public function __set($property, $value){

		$method = 'set'.$this->_convertPropertyToMethod($property);

		if(!$this->_property_raw_mode && !$this->_property_force && method_exists($this, $method)){
			if($this->_metadata->hasProperty($property)){
				if(!call_user_func(array($this, $method), $value)){
					throw new Exception('Unable to set value: setter method "'.$property.'" returned false.');
				}
			} else {
				throw new Exception('Unable to call setter method when property have not been explicitly declared');
			}
		} else {
			if(!$meta_property = $this->_metadata->getMetadataProperty($property)){
				throw new Exception('Unable to find Property field, no property field declared for "'.$property.'" ('.$this->_convertPropertyToField($property).')');
			}
			// print_r($meta_property);
			if($this->_metadata->hasProperty($property)){
				$propertyReflection = $this->_metadata->getProperty($property);
				if($propertyReflection->isPrivate()){
					throw new Exception('Unable to access private property: "'.$property.'"');
				} else {
					$this->$property = $value;
				}
			} else {
				$this->_properties[$property] = $value;
			}
			if(!$this->_property_raw_mode){
				if(!$datafield = $meta_property->getValue('datafield')){
					return $this->datahandler->set($property, $value);
				} else {
					return $this->datahandler->set($datafield, $value);
				}
			} else {
				return true;
			}
		}
		return true;
	}
	*/
/*
	public function __get($property){
		$method = 'get'.$this->_convertPropertyToMethod($property);
		if(!$this->_property_force && method_exists($this, $method)){
			if(!is_callable(array($this, $method))){
				throw new Exception('Unable to get value, value is read only');
			} else {
				return call_user_func(array($this, $method));
			}
		} else {
			if($this->_metadata->hasMetadataProperty($property)){
				if(array_key_exists($property, $this->_properties)){
					if($this->_hasConverter($property)){
						return $this->_getConverter($property)->convert($this->_properties[$property]);
					} else {
						return $this->_properties[$property];
					}
				} else if($this->_metadata->hasProperty($property)){
					$propertyReflection = $this->_metadata->getProperty($property);
					if($propertyReflection->isPrivate()){
						throw new Exception('Unable to access private property: "'.$property.'"');
					} else {
						return $this->$property;
					}
				}
				return null;
			} else {
				throw new Exception('Undefined property: "'.$property.'"');
			}
		}
	}
*/

	public function __isset($property){
		throw new Exception('isset is not allowed');
		return ($this->_getPropertyField($property) ? true : false);

	}

	public function __unset($property){
		throw new Exception('Unset is not allowed');
	}

	public function __call($method, $args){
		if(substr($method, 0,3) == 'set'){
			if($property = $this->_getPropertyFromMethod($method, 'set', 'Converter')){
				if($args[0] instanceof Converter){
					return $this->_setConverter($property, $args[0]);
				} else {
					throw new Exception('Call to '.get_class($this).'::'.$method.'() expected first argument to be instance of class \Converter');
				}
			} else {

				$property = $this->_convertMethodToProperty(substr($method, 3));
				if(!$meta_property = $this->_metadata->getMetadataProperty($property)){
					throw new Exception('Unable to find Property field, no property field declared for "'.$property.'" ('.$this->_convertPropertyToField($property).')');
				}
				if($meta_property->hasValue('readonly')){
					throw new Exception('Unable to write property, property is marked readonly');
				}

				return $this->_setProperty($property, $args[0]);
			}
		} else if(substr($method, 0,3) == 'get'){
			$property = $this->_convertMethodToProperty(substr($method, 3));
			$object_instance = true;
			if(!$this->_metadata->hasMetadataProperty($property)){
				if(substr($property, -2) == 'id' && $this->_metadata->hasMetadataProperty(substr($property, 0, -3))){
					$property = substr($property, 0, -3);
					$object_instance = false;
				} else if(substr($property, -3) == 'i_d' && $this->_metadata->hasMetadataProperty(substr($property, 0, -4))){
					$property = substr($property, 0, -4);
					$object_instance = false;
				}
			}

			if($this->_metadata->hasMetadataProperty($property)){
				$meta_property = $this->_metadata->getMetadataProperty($property);
				$value = $this->_getProperty($property, true);

				if($object_instance && $meta_property->getValue('type') == 'class'){
					if(!$classname = $meta_property->getValue('classname')){
						throw new Exception('Missing class value for property: '.$property);
					}
					return new $classname();
				}
				return $value;
			}

		}

		throw new Exception($this->_metadata->getName().'::'.$method.'() was not found');
		return false;
	}

	public static function __callStatic($method, $args){
		$class = get_called_class();

		if(substr($method, 0,5) == 'getBy'){
			$object = new $class();
			$property = $object->_convertMethodToProperty(substr($method, 5));
			if($meta_property = $object->_metadata->getMetadataProperty($property)){
				if($meta_property->hasValue(self::DATA_ACCESS_METADATA_PRIMARY)){
					$properties = $object->_metadata->searchMetadataProperties(self::DATA_ACCESS_METADATA_PRIMARY, true);
					if(sizeof($properties) == sizeof($args)){
						if(!$object->_getFromProperties($properties, $args)){
							return false;
						}
					} else {
						throw new Exception('Expected two arguments for function call');
					}
				}
			}
			return $object;
		}
		throw new Exception('Static method: '.$class.'::'.$method.'() was not found');
		return false;
	}

	public function __invoke($property, $value=null){
		assert('is_string($property)');

		if(is_null($value)){
			return $this->_getProperty($property);
		} else {
			return $this->_setProperty($property, $value);
		}
	}

	protected function _setProperty($property, $value, $raw=false){
		$metadata = $this->_metadata->getMetadataProperty($property);
		$property_reference = &$this->_getPropertyReference($property);
		$property_reference = $value;

		if(!$raw){
			if(!$datafield = $metadata->getValue(self::DATA_ACCESS_METADATA_DATAFIELD)){
				return $this->datahandler->set($property, $value);
			} else {
				return $this->datahandler->set($datafield, $value);
			}
		}

		/*
		$_property_raw_mode = $this->_property_raw_mode;
		$_property_force = $this->_property_force;

		try {
			$this->_property_raw_mode = $raw;
			$this->_property_force = $force;
			$this->__set($property, $value);
			// $this->$property = $value;
			$this->_property_raw_mode = $_property_raw_mode;
			$this->_property_force = $_property_force;
		} catch (\Exception $e){
			// Disable raw mode even if a exception was thrown
			$this->_property_raw_mode = $_property_raw_mode;
			$this->_property_force = $_property_force;
			throw $e;
		}
		*/
	}

	protected function _getProperty($property){
		return $this->_getPropertyReference($property);


		/*
		$_property_force = $this->_property_force;

		try {
			$this->_property_force = $force;
			return $this->__get($property);
			$this->_property_force = $_property_force;
		} catch (\Exception $e){
			// Disable raw mode even if a exception was thrown
			$this->_property_force = $_property_force;
			throw $e;
		}
		*/
	}

	/**
	 * Find reference to property value.
	 *
	 * Return a pointer to where ever the property data is located.
	 *
	 * @param $property
	 *
	 * @return mixed
	 * @throws Exception
	 */
	private function &_getPropertyReference($property){
		$metadata = $this->_metadata->getMetadataProperty($property);
		if($this->_metadata->hasProperty($property)){
			$propertyReflection = $this->_metadata->getProperty($property);
			if($propertyReflection->isPrivate()){
				throw new Exception('Unable to access private property: "'.$property.'"');
			}
			return $this->$property;
		} else {
			if(!isset($this->_properties[$property])){
				$this->_properties[$property] = null;
			}
			return $this->_properties[$property];
		}
	}

	protected function _setFromArray(array &$array){
		try {
			$i = 0;
			while(list($key, $val) = each($array)){
				if($datatype = $this->_metadata->getMetadataProperty($key)->getValue('type')){
					$i++;
					switch($datatype){
						case 'int':
						case 'integer':
						case 'timestamp':
							$val = (int) $val;
							break;
						case 'bool':
						case 'boolean':
							$val = (boolean) $val;
							break;

					}
					$this->_setProperty($key, $val, true);
				}
			}
		} catch (\Exception $e){
			reset($array);
			throw $e;
		}
		reset($array);
		return $i;
	}

	private function _getFromProperties(array $properties, array $values){
		if($array = $this->dao->getFromProperties($this->_metadata, $properties, $values)){
			$this->_setFromArray($array);
			return true;
		} else {
			return false;
		}
	}
}
?>