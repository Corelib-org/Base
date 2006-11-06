<?php
class PageFactoryMetaPageResolver implements PageFactoryPageResolver {
	static public function resolve($expr, $exec){
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
					$function = ($key + 1);
					break;
				case 'string':
					$expr = str_replace($val, '[a-z]+', $expr);
					$param[] = '(string) \'\\'.($val + 1).'\'';
					break;
			}
			
		}
		$expr = '/^'.str_replace('/', '\/', $expr).'/';
		$exec = '\\'.$function.'('.implode(', ', $param).')';
		return array($expr, $exec);
	}
}
?>