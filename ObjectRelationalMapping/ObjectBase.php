<?php
namespace Corelib\Base\ObjectRelationalMapping;

abstract class ObjectBase {

	private $converters = array();


	protected function _convertPropertyToMethod($property){
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
	}

	protected function _convertMethodToProperty($method, $separator='_'){
		$method = strtolower(preg_replace('/[A-Z]/', $separator.'$0', $method));
		if($method{0} == $separator){
			$method = substr($method, 1);
		}
		return $method;
	}

	protected function _getPropertyValues(array $properties){
		$values = array();
		foreach ($properties as $key => $val) {
			$values[$key] = $this->_getProperty($key, true);
		}
		return $values;
	}

	protected function _setConverter($property, \Converter $converter){
		$this->converters[$property] = $converter;
		return true;
	}

	protected function _getConverter($property){
		return $this->converters[$property];
	}

	protected function _getConverters(){
		return $this->converters;
	}

	protected function _hasConverter($property){
		return isset($this->converters[$property]);
	}

	protected function _getPropertyFromMethod($method, $prefix, $suffix=null){
		$prefix_length = strlen($prefix);
		if(substr($method, 0, $prefix_length) == $prefix){
			$method = substr($method, $prefix_length);
			if(!is_null($suffix)){
				$suffix_length = strlen($suffix) * -1;
				if(substr($method, $suffix_length) == 'Converter'){
					return $this->_convertMethodToProperty(substr($method, 0, $suffix_length));
				}
			} else {
				return $this->_convertMethodToProperty($method);
			}
		}
		return false;
	}
}