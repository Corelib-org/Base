<?php
class PasswordGenerator {
	private static $characters = 'ABCDEFGHKMNPRSTXYZabcdefghjkmnpqrtxyz2346789';
	
	static public function random($len){
		$code = '';
		for ($i = 1; $i <= $len; $i++){	
			$code .= self::$characters{mt_rand(0, (strlen(self::$characters) -1 ))};
		}
		return $code;
	}	
}
?>