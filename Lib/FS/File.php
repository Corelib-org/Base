<?php
class File {
	private $filename = null;
	private $mime_type = null;
	
	public function __construct($file){
		if(!is_file($file)){
			$this->filename = realpath(dirname($file)).'/'.$file;
		} else {
			$this->filename = realpath($file);
		}
	}
	
	public function getName(){
		return basename($this->filename);
	}
	public function getFullName(){
		return $this->filename;
	}
	public function getPath(){
		return dirname($this->filename());
	}
	public function getMimeType(){
		return $this->mime_type;
	}
	public function getSize(){
		return filesize($this->getFullName());
	}
	
	
	public function cp($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getFullName()){
				throw new BaseException('Unable to copy file it self');
			} else {
				if(copy($this->getFullName(), $target)){
					new File($target);
				} else {
					return false;
				}
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
	}
	public function mv($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getFullName()){
				throw new BaseException('Unable to copy file it self');
			} else {
				if(rename($this->getFullName(), $target)){
					$this->filename = $target;
					return $this;
				} else {
					return false;
				}
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
	}	
	public function rm(){
		return unlink($this->getFullName());
	}
	
	protected function _resolveTarget($target){
		if(is_dir($target)){
			$target = realpath($target).'/'.$this->getName();
		} else {
			$dir = dirname($target);
			try {
				if(is_dir($dir)){
					$target = realpath($dir).'/'.basename($target);
				} else {
					throw new BaseException('Unable to resolve path, no such file or directory', E_USER_ERROR);
				}
			} catch (BaseException $e){
				echo $e;
				return false;
			}
		}
		return $target;
	}
	protected function _setMimeType($type){
		$this->mime_type = $type;
	}
}
?>