<?php
class PasswordGenerator {
	private $characters = 'ABCDEFGHKMNPRSTXYZabcdefghjkmnpqrtxyz2346789';
	
	static public function random($len){
		$code = '';
		for ($i = 1; $i <= $len; $i++){	
			$code .= $this->characters{rand(0, (strlen($this->characters) -1 ))};
		}
		return $code;
	}	
}
?>