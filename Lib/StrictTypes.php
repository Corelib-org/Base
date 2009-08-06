<?php
/**
 * @todo add proper documentation
 * @todo should be moved to it's own module
 */
class StrictTypes {
	static public function isString($subject, $length=null){
		if(BASE_RUNLEVEL != BASE_RUNLEVEL_PROD){
			$error = false;
			if(!is_string($subject)){
				$error = true;
			} 
			if(!is_null($length)){
				if(self::isInteger($length)){
					if(strlen($subject) > $length){
						$error = true;
					}
				}
			}
			if($error){
				throw new StrictTypeStringException($subject, $length);
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	static public function isInteger($subject){
		if(!is_integer($subject)){
			throw new StrictTypeIntegerException($subject);
			return false;
		} else {
			return true;
		}
	}
	static public function isFloat($subject){
		if(!is_float($subject)){
			throw new StrictTypeFloatException($subject);
			return false;
		} else {
			return true;
		}
	}
	static public function isArray($subject){
		if(!is_array($subject)){
			throw new StrictTypeArrayException($subject);
			return false;
		} else {
			return true;
		}
	}
	static public function isBoolean($subject){
		if(!is_bool($subject)){
			throw new StrictTypeBooleanException($subject);
			return false;
		} else {
			return true;
		}
	}
	static public function isRescource($subject){
		if(!is_resource($subject)){
			throw new StrictTypeRescourceException($subject);
			return false;
		} else {
			return true;
		}
	}
	
	static public function toString($subject){
		return (string) $subject;
	}
	static public function toBoolean($subject){
		return (boolean) $subject;
	}
	static public function toInteger($subject){
		return (integer) $subject;
	}
	static public function toFloat($subject){
		return (float) $subject;
	}
}

class StrictTypeException extends BaseException {
	protected function getTypeData($subject){
		return gettype($subject).'('.strlen($subject).') "'.$subject.'"';
	}
}
class StrictTypeStringException extends StrictTypeException {
	public function __construct($subject, $length=null){
		if(!is_numeric($length)){
			$description = 'Paramemeter is not of type string.';
		} else {
			$description = 'Paramemeter is not of type string, or is more then '.$length.' bytes long';
		}
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
class StrictTypeIntegerException extends StrictTypeException {
	public function __construct($subject){
		$description = 'Paramemeter is not of type integer.';
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
class StrictTypeFloatException extends StrictTypeException {
	public function __construct($subject){
		$description = 'Paramemeter is not of type float.';
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
class StrictTypeArrayException extends StrictTypeException {
	public function __construct($subject){
		$description = 'Paramemeter is not of type Array.';
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
class StrictTypeBooleanException extends StrictTypeException {
	public function __construct($subject){
		$description = 'Paramemeter is not of type boolean.';
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
class StrictTypeRescourceException extends StrictTypeException {
	public function __construct($subject){
		$description = 'Paramemeter is not of type rescource.';
		parent::__construct($description.' '.$this->getTypeData($subject), E_USER_WARNING);
	}
}
?>