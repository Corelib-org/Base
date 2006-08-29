<?php
class HEXHide {
	public static function hide($number, $custom_seed=null){
		$md5 = sha1(microtime()).md5(strrev(microtime()));
		$offset = self::_getHiddenOffset($md5);
		$prefix = substr($md5, 0, $offset);
		$length = dechex(strlen($number));
		$end = substr($md5, strlen($prefix) + strlen($number)+strlen($length));
		return $prefix.$length.$number.$end;
	}
	public static function find($string){
		$offset = self::_getHiddenOffset($string);
		$offset = substr($string, $offset, strlen($string));
		$endbytes = hexdec($offset{0});
		return substr($offset, 1, $endbytes);
	}
	private static function _getHiddenOffset($hex){
		$offset = hexdec($hex{0});
		if($offset == 0){
			$offset++;
		}
		$new_offset = hexdec($hex{$offset});
		if($new_offset == 0){
			$new_offset++;
		}
		$offset += $new_offset;
		$new_offset = hexdec($hex{$offset});
		if($new_offset == 0){
			$new_offset++;
		}
		$offset += $new_offset;
		return $offset;
	}
}
?>