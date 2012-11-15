<?php

namespace Corelib\Base\ObjectRelationalMapping\Metadata;

class Property {

	private $values = array();
	private $name;

	public function __construct($name, array $values){
		$this->values = $values;
		$this->name = $name;
	}

	public function getName(){
		return $this->name;
	}

	public function hasValue($value){
		return isset($this->values[$value]);
	}

	public function getValue($value){
		if($this->hasValue($value)){
			return $this->values[$value];
		}
		return false;
	}

	public function getValues(){
		return $this->values;
	}
}
?>