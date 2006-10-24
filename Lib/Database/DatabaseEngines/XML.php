<?php
class XMLEngine implements DatabaseEngine {
	private $xml_base_dir = 'var/db/xml/';
	private $xml_cache_enable = false;
	private $xml_cache = array();

	const PREFIX = 'XML';
	
	public function __construct($xmlbasedir = 'var/db/xml/', $cache_xml=false){
		try {
			StrictTypes::isString($xmlbasedir);
			StrictTypes::isBoolean($cache_xml);
		} catch (BaseException $e){
			echo $e;
		}
		$this->xml_base_dir = $xmlbasedir;
		$this->xml_cache_enable = $cache_xml;
	}
	
	public function query(Query $query){
		try {
			if(!$query instanceof XMLQuery){
				throw new BaseException('Invalid Query Object, object must be instance of XMLQuery');	
			}
		} catch (BaseException $e){
			echo $e;	
		}
		if($this->xml_cache_enable){
			$query->setInstance(&$this->xml_cache);
		}
		$query->execute();
	}
	
	public function getPrefix(){
		return self::PREFIX;
	}
	public function startTransaction(){ }
	public function commit(){ }
	public function rollback(){ }
}

?>