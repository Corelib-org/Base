<?php
class PasswordGenerator {
	private static $characters = 'ABCDEFGHKMNPRSTXYZabcdefghjkmnpqrtxyz2346789';
	
	static public function random($len){
		$code = '';
		for ($i = 1; $i <= $len; $i++){	
			$code .= self::$characters{rand(0, (strlen(self::$characters) -1 ))};
			$code .= $this->characters{mt_rand(0, (strlen($this->characters) -1 ))};
		}
		return $code;
	}	
}
?>