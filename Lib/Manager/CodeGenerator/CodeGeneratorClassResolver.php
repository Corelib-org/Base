<?php
class CodeGeneratorClassResolver implements Singleton {
	/**
	 * @var CodeGeneratorClassResolver
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $lookup_table = array();

	/**
	 *	@return CodeGeneratorClassResolver
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new CodeGeneratorClassResolver();
		}
		return self::$instance;
	}

	public function __construct(){
		set_time_limit(0);
		$this->_buildLookupTable();
	}

	public function getClass($key){

		if(isset($this->lookup_table[$key])){
			return $this->lookup_table[$key];
		} else {
			return false;
		}
	}

	public function addClass($key, $class){
		$this->lookup_table[$key] = $class;
	}

	private function _buildLookupTable(){
		$array = Base::getInstance()->getClassPaths();
		foreach ($array as $dir){
			$this->_searchDir($dir);
		}
	}

	private function _searchDir($dir){
		$fp = dir($dir);
		while($entry = $fp->read()){
			if($entry{0} != '.' && is_dir($dir.'/'.$entry)){
				if($file = $this->_searchDir($dir.'/'.$entry)){
					return $file;
				}
			} else if($entry{0} != '.' && is_readable($dir.'/'.$entry) && strstr($entry, '.php')){
				$content = file($dir.'/'.$entry);
				$inclass = false;
				foreach ($content as $line){
					if(preg_match('/class\s(.*?)\s.*?{/', $line, $match)){
						$inclass = $match[1];
					} else if ($inclass !== false && preg_match('/const\sFIELD_ID\s=\s[\'"](.*?)[\'"];/', $line, $match)){
						$this->lookup_table[$match[1]] = $inclass;
						$inclass = false;
					}

				}
			}
		}
		return false;
	}
}
?>