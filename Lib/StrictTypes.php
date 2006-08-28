<?php
class StrictTypes {
	static public function isString($subject, $length=null){
		$error = false;
		if(!is_string($subject)){
			$error = true;
		} 
		if(!is_null($length)){
			if(self::isInteger($length)){
				if(strlen($subjcet) > $length){
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
	}
	static public function isInteger($subject){
		if(!is_integer($subject)){
			throw new StrictTypeIntegerException($subject);
			return false;
		} else {
			return true;
		}
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
?>