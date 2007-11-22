<?php
class PageFactoryDOMXSLCapsule {
	public static $data = array();

	public static function parseCacheData($page, &$settings, &$content){
		self::$data = array('settings'=>&$settings, 'content'=>&$content);
		return self::_eval($page);
	}

	private static function _eval(&$code){
		// echo highlight_string($code);
		return eval('?>'.$code);
	}

	public static function valueOf($data){
		return $data;
	}
	public static function copyOf($data){
		return $data;
	}

	private static function each(&$data){
		static $pos = 1;
		if(is_array($data)){
			if(list($key, $val) = each($data)){
				if(is_numeric($key)){
					$keys = array_keys($val);
					$tag = array_pop($keys);
					$val = array_pop($val);
					return array($pos, $tag, $val);
				} else {
					return array($pos, $key, $val);
				}
				$pos++;
			} else {
				$pos = 1;
				reset($data);
				return false;
			}
		} else {
			return $data;
		}
	}

	private static function read($var){
		return $var;
	}

	public static function applyTemplates(&$data, $tag=false){
		while(list($POSITION, $TAG, $CURRENT) = self::each($data)){
			if(($tag && $tag == $TAG) || !$tag){
				$eval = 'PageFactoryDOMXSLMatchTemplates::'.$TAG.'($POSITION, $TAG, $CURRENT);';
				eval($eval);
			}
		}
	}



	public static function dump($data){
		return "\n".self::_dump($data);
	}

	private static function _dump($data, $prefix='', $return=''){
		while (list($key, $val) = each($data)) {
			if(is_array($val)){
				$return = self::_dump($val, $prefix.'/'.$key, $return);
			} else {
				$return .= $prefix.'/'.$key.' = '.$val."\n";
			}
		}
		return $return;
	}
}
?>