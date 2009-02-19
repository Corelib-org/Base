<?php
abstract class CodeGeneratorFile implements Output {
	private $filename = null;
	private $classname = null;
	private $path = null;
	private $table = null;
	
	private $write_result = null;
	
	private $template = null;
	
	private $is_nm_table = null;
	
	protected $content = null;
	protected $fields = null;
	
	public function __construct($path, $classname, $table, $fields){
		$this->path = $path;
		$this->table = $table;
		$this->classname = $classname;
		$this->fields = &$fields;
	}
	
	abstract public function generate();
	
	public function write(){
		$this->_loadContent();
		$this->generate();
		
		$this->content = trim($this->content);
		if(!is_dir(dirname($this->getFilename()))){
			mkdir(dirname($this->getFilename()), 0777, true);
		}
		if(is_file($this->getFilename())){
			if(md5_file($this->getFilename()) != md5($this->content)){
				$tempname = tempnam('var/tmp', 'diff');
				file_put_contents($tempname, $this->createPatch());
				chmod($tempname, 0666);
				$command = 'patch '.$this->getFilename().' -i '.$tempname.' 2>&1';
				exec($command, $result);
				$this->write_result = implode("\n", $result);
				unlink($tempname);
			}
		} else {
			file_put_contents($this->getFilename(), $this->content);
		}
	}
	
	public function getFilename(){
		return $this->path.$this->filename;
	}
	
	public function getXML(DOMDocument $xml){
		$action = $xml->createElement('file');
		$action->setAttribute('filename', $this->getFilename());
		
		if(is_file($this->getFilename())){
			if(md5_file($this->getFilename()) == md5($this->content)){
				$action->setAttribute('action', 'none');
			} else {
				$action->setAttribute('action', 'patch');
				if(!is_null($this->write_result)){
					$action->appendChild($xml->createTextNode($this->write_result));
				} else {
					$patch = htmlspecialchars($this->createPatch());
					$patch = preg_replace('/^(-{1}(\s|\?).*?)$/m', '<span class="GeneratorLineRemove">\\1</span>', $patch);
					$patch = preg_replace('/^(\+{1}(\s|\?).*?)$/m', '<span class="GeneratorLineAdd">\\1</span>', $patch);
					$action->appendChild($xml->createTextNode($patch));
				}
			}
		} else {
			$action->setAttribute('action', 'create');
		}
		return $action;
	}	
	
	protected function _setFilename($filename){
		 $this->filename = $filename;
	}
	
	protected function createPatch(){
		$tempfile = tempnam('var/tmp', 'diff');
		file_put_contents($tempfile, $this->content);
		$diff = 'diff -usN '.str_replace('//', '/', $this->getFilename()).' '.$tempfile;
		$diff = trim(`$diff`);
		unlink($tempfile);
		return $diff;
	}
	
	protected function _writeClassName(&$content){
		$content = str_replace('${classname}', $this->classname, $content);
	}
	protected function _writeTableName(&$content){
		$content = str_replace('${tablename}', $this->table, $content);
	}	
	protected function _writeClassVar(&$content){
		$content = str_replace('${classvar}', $this->_getClassVar(), $content);
	}	
	
	protected function _getClassVar(){
		return strtolower($this->classname);
	}	
	protected function _getClassName(){
		return $this->classname;
	}	
	
	protected function _getTableName(){
		return $this->table;
	}
	
	protected function _getCommentBlock(array $source, $offset){
		$comment = '';
		$in_comment = false;
		for ($i = $offset - 1; $i >= 0; $i--){
			if(preg_match('/\*\//', $source[$i]) || $in_comment === true){
				$in_comment = true;
				$comment = $source[$i]."\n".$comment;
				if(preg_match('/\/\*\*/', $source[$i], $match)){
					break;
				}
			} else {
				break;
			}
		}
		return $comment;
	}
	protected function _getCodeBlock(&$source, $block, $prefix='/*', $suffix='*/'){
		$pcre = '/'.preg_quote($prefix,'/').'\s*'.$block.'\s*'.preg_quote($suffix,'/').'\n(.*?)\t'.preg_quote($prefix,'/').'\s*'.$block.' end\s*'.preg_quote($suffix,'/').'/s';
		if(preg_match($pcre, $source, $match)){
			return $match[1]."\n";
		} else {
			return false;
		}
	}
	protected function _writeCodeBlock(&$source, $block, $code, $prefix='/*', $suffix='*/'){
		$source = preg_replace('/('.preg_quote($prefix,'/').'\s*'.$block.'\s*'.preg_quote($suffix,'/').'\n)(.*?)(\t*'.preg_quote($prefix,'/').'\s*'.$block.' end\s*'.preg_quote($suffix,'/').')/s', '\\1'.$code.'\\3', $source);
	}
	protected function _createMethodName($prefix, $field){
		$field = explode('_', $field);
		$field = array_map('ucfirst', $field);
		$field = implode('', $field);
		if(strtolower($field) == 'id'){
			$field = strtoupper($field);
		}
		return $prefix.$field;
	}
	protected function _makeMethod($name, $source, $docblock=null, $param='', $visibility='public'){
		$method = '';
		if(!is_null($docblock)){
			$method .= "\t".$docblock;
		}
		$method .= "\t".$visibility.' function '.$name.'('.$param.'){'."\n";
		$source = explode("\n", $source);
		foreach ($source as $line){
			$method .= "\t\t".$line."\n";
		}
		$method .= "\t".'}';
		return $method;
	}

	protected function _loadContent($filename=null){
		if(!is_null($filename)){
			$this->template = $filename;
		}
		$filename = $this->template;
		
		if(is_file($this->getFilename())){
			$this->content = file_get_contents($this->getFilename());
		} else {
			$this->content = file_get_contents($filename);
		}
	}
	
	protected function _isNMRelationTable(){
		if(is_null($this->is_nm_table)){
			foreach ($this->fields as $field){
				if(preg_match('/^pk_/', $field['field'])){
					$this->is_nm_table = false;
					break;
				}
			}
			if(is_null($this->is_nm_table)){
				$this->is_nm_table = true;
			}
		}
		return $this->is_nm_table;
	}
	
	protected function _getPrimaryFields(){
		$fields = array();
		foreach ($this->fields as $field){
			if(isset($field['keytype']) && $field['keytype'] == 'primary'){
				$fields[] = $field;
			}
		}
		return $fields;
	}
}
?>