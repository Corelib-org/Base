<?php
abstract class CodeGeneratorModelFileBase extends CodeGeneratorFile {
	protected $content_tables = array();

	public function addContentTable($table, $data){
		foreach ($data as $key => $field){
			$data[$key]['sortable'] = false;
			$data[$key]['unique'] = false;
		}
		$this->content_tables[$table] = $data;
	}
}
?>