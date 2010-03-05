<?php
/**
 * @todo maybe this should be moved to a sublibrary
 */
define('DATABASE_MYSQLI_VIEW_JOIN_TABLE', 'mysqli_view_join_table');
define('DATABASE_MYSQLI_VIEW_JOIN_KEY', 'mysqli_view_join_key');
define('DATABASE_MYSQLI_VIEW_WHERE', 'mysqli_view_where');

define('DATABASE_VIEW_XML_FIELD', 'xml');

class DatabaseViewHelper extends DatabaseListHelper {
	public function __construct(){
		$this->set(DATABASE_VIEW_XML_FIELD, DATABASE_VIEW_XML_FIELD);
	}
}

class DatabaseDAOView extends DatabaseDAO {
	/**
	 * @var DatabaseViewHelper
	 */
	private $view = null;

	public function getListHelper(){
		try {
			if(is_null($this->view)){
				throw new BaseException('DatabaseViewHelper not set.', E_USER_ERROR);
			} else {
				return $this->view;
			}
		} catch (Exception $e){
			echo $e;
		}
	}

	protected function _setListHelper(DatabaseViewHelper $view){
		$this->view = $view;
		return true;
	}
}

interface ViewList {
	public function getViewXML($id, array $array = array(), DOMDocument $xml);
	public function getListHelper();
}

abstract class View implements Output {
	protected $xml = null;

	protected $dao = null;

	const FIELD_XML = 'xml';

	abstract public function commit();
	/**
	 * @return DOMElement
	 */
	abstract protected function _generate(DOMDocument $xml);
	abstract protected function _getDAO($read=true);

	public function generate(){
		$this->_getDAO(false);
		if(is_null($this->xml)){
			$this->xml = $this->_prepareDOMDocument();
		}
		$this->xml->appendChild($this->_generate($this->xml));
		$this->commit();
	}

	public function getXML(DOMDocument $xml){
		if(is_null($this->xml) && !$this->read()){
			$this->generate();
		}
		return $xml->importNode($this->xml->documentElement, true);
	}

	public function &getArray(){

	}

	protected function _getXML(){
		return $this->xml->saveXML();
	}

	protected function _loadXML($xml){
		$this->xml = $this->_prepareDOMDocument();
		$this->xml->loadXML($xml);;
	}

	protected function _setFromArray($array){
		if(!is_null($array[self::FIELD_XML])){
			$this->_loadXML($array[self::FIELD_XML]);
		}
	}

	private function _prepareDOMDocument(){
		return new PageFactoryDOMXSLDOMDocument('1.0', 'UTF-8');
	}
}
?>