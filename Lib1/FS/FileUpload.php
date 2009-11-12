<?php
class FileUpload extends File {
	private $error = null;
	private $original_filename = null;
	
	public function __construct($upload){
		try {
			if(isset($_FILES[$upload])){
				$this->error = $_FILES[$upload]['error'];
				$this->original_filename = $_FILES[$upload]['name'];
				$this->_setMimeType($_FILES[$upload]['type']);
				if($_FILES[$upload]['error'] == UPLOAD_ERR_OK){
					parent::__construct($_FILES[$upload]['tmp_name']);
				}
			} else {
				throw new BaseException('File upload identifier not found: '.$upload, E_USER_WARNING);
			}
		} catch (BaseException $e){
			echo $e;
		}
	}
	
	public function getError(){
		return $this->error;
	}
	
	public function getName(){
		return $this->original_filename;
	}
	
	public function mv($target){
		$target = $this->_resolveTarget($target);
		try {
			if($target == $this->getFullName()){
				throw new BaseException('Unable to copy file to it self');
			} else {
				if(move_uploaded_file($this->getFullName(), $target)){
					return new File($target);
				} else {
					return false;
				}
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}		
	}
	
}
?>