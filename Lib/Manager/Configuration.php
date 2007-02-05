<?php
class Configuration implements Singleton,Output {
	private static $instance = null;

	private $groups = array('Misc'=>array());

	private $updated = false;

	const CONSTANT_REGISTRY = 'constants.db';

	private function __construct(){
		if(is_file(MANAGER_DATADIR.self::CONSTANT_REGISTRY)){
			$this->groups = unserialize(file_get_contents(MANAGER_DATADIR.self::CONSTANT_REGISTRY));
		}
	}

	/**
	 *	@return Configuration
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Configuration();
		}
		return self::$instance;
	}

	public function loadConstantsFile($filename){
		try {
			StrictTypes::isString($filename);
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->load($filename);
		if(!$group = $xml->documentElement->getAttribute('group')){
			$group = 'Misc';
		}
		$constants = $xml->getElementsByTagName('constant');
		if($constants->length > 0){
			$i = 0;
			while($constant = $constants->item($i++)){
				$pi = 0;

				$name = null;
				$title = '';
				$desc = '';
				$readonly = false;
				$fieldtype = 'string';
				$type = 'string';
				$default = '';

				$readonly = $constant->getAttribute('readonly');
				if($readonly == 'true'){
					$readonly = true;
				} else {
					$readonly = false;
				}
				while($propeties = $constant->childNodes->item($pi++)){
					switch ($propeties->nodeName){
						case 'name':
							$name = $propeties->nodeValue;
							break;
						case 'title':
							$title = $propeties->nodeValue;
							break;
						case 'desc':
							$desc = $propeties->nodeValue;
							break;
						case 'value':
							if($propeties->getAttribute('fieldtype')){
								$fieldtype = $propeties->getAttribute('fieldtype');
							}
							if($propeties->getAttribute('type')){
								$type = $propeties->getAttribute('type');
							}
							if($propeties->getAttribute('default')){
								$default = $propeties->getAttribute('default');
							}
							break;
					}
				}
				if($bind = $constant->getAttribute('bind')){
					$this->groups[$group][$bind]['binds'][$name]['title'] = $title;
					$this->groups[$group][$bind]['binds'][$name]['desc'] = $desc;
					$this->groups[$group][$bind]['binds'][$name]['readonly'] = $readonly;
					$this->groups[$group][$bind]['binds'][$name]['fieldtype'] = $fieldtype;
					$this->groups[$group][$bind]['binds'][$name]['type'] = $type;
					$this->groups[$group][$bind]['binds'][$name]['default'] = $default;
				} else {
					$this->groups[$group][$name]['title'] = $title;
					$this->groups[$group][$name]['desc'] = $desc;
					$this->groups[$group][$name]['readonly'] = $readonly;
					$this->groups[$group][$name]['fieldtype'] = $fieldtype;
					$this->groups[$group][$name]['type'] = $type;
					$this->groups[$group][$name]['default'] = $default;
				}
			}
			file_put_contents(MANAGER_DATADIR.self::CONSTANT_REGISTRY, serialize($this->groups));
		}
	}

	public function getXML(DOMDocument $xml){
		$c = $xml->createElement('constants');
		while(list($key, $val) = each($this->groups)){
			$g = $c->appendChild($xml->createElement('group'));
			$g->setAttribute('title', $key);
			if(is_array($val)){
				while (list($constant,$info) = each($val)) {
					$const = $g->appendChild($this->_makeConstantNode($xml, $constant, $info));
					if(isset($info['binds']) && is_array($info['binds'])){
						$binds = $const->appendChild($xml->createElement('binds'));
						while (list($bind, $data) = each($info['binds'])) {
							$binds->appendChild($this->_makeConstantNode($xml, $bind, $data));
						}
					}
				}
			}
		}
		return $c;
	}

	private function _makeConstantNode(DOMDocument $xml, $constant, $data){
		$const = $xml->createElement('constant');
		$const->setAttribute('name', $constant);
		if($data['readonly']){
			$const->setAttribute('readonly', 'true');
		}
		$const->setAttribute('fieldtype', $data['fieldtype']);
		$const->setAttribute('type', $data['type']);
		$const->appendChild($xml->createElement('title', $data['title']));
		$const->appendChild($xml->createElement('desc', $data['desc']));
		$const->appendChild($xml->createElement('default', $data['default']));
		return $const;
	}

	public function &getArray(){
		return $this->groups;
	}
}

class ConfigurationLoadConstantsFile implements EventTypeHandler,Observer  {
	private $subject = null;
	/**
	 * @var Configuration
	 */
	private $config = null;

	public function __construct(){
		$this->config = Configuration::getInstance();
	}

	public function getEventType(){
		return 'ManagerFileSearch';
	}

	public function register(ObserverSubject &$subject){
		$this->subject = $subject;
	}

	public function update($update){
		$filename = $update->getFilename();
		$basename = basename($filename);
		if($basename == 'constants.xml'){
			$this->config->loadConstantsFile($filename);
		}
	}
}

$event = EventHandler::getInstance();
$event->registerObserver(new ConfigurationLoadConstantsFile());
?>