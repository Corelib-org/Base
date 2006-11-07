<?php
class MetaResolver implements PageFactoryPageResolver {
	private $exec = null;
	private $expr = null;
	
	public function resolve($expr, $exec){
		preg_match_all('/\((.*?)\)/', $expr, $result);
		
		$param = array();
		while(list($key, $val) = each($result[1])){
			if(strstr($val, ':')){
				list($type, $name) = explode(':', $val);
			} else {
				$type = 'function';
				$name = $val;
			}
			switch ($type) {
				case 'int':
					$expr = str_replace($val, '[0-9]+', $expr);
					$param[] = '(int) \\'.($key + 1).'';
					break;
				case 'function':
					$expr = str_replace($val, '[a-z]+', $expr);
					$function = '\\'.($key + 1);
					break;
				case 'string':
					$expr = str_replace($val, '[a-z]+', $expr);
					$param[] = '(string) \'\\'.($key + 1).'\'';
					break;
			}
		}
		if(strstr($exec, ':')){
			list($function) = explode(':', $exec, 2);
		}
		$expr = '/^'.str_replace('/', '\/', $expr).'$/';
		$exec = $function.'('.implode(', ', $param).')';
		$this->exec = $exec;
		$this->expr = $expr;
		return true;
	}
	
	public function getExpression(){
		return $this->expr;
	}
	public function getExecute(){
		return $this->exec;
	}
}
?>