<?php
namespace Corelib\Base\ObjectRelationalMapping\Metadata;
use Corelib\Base\ObjectRelationalMapping\Exception;
use Corelib\Base\PageFactory\Output;

class Parser extends \ReflectionClass implements Output {

	private $properties = array();
	private $search = array();
	private $reflection = array();

	public function __construct($class){
		if(is_object($class)){
			parent::__construct(get_class($class));
		} else {
			parent::__construct($class);
		}

		$doc = $this->getDocComment();
		$doc = trim(preg_replace('/^\s*((\/\**)|(\s*\*\s)|(\*\/))/m', '', $doc));
		$doc = trim(preg_replace('/(^\s*)|(\s*$)/m', '', $doc));

		if(preg_match_all('/^\s*@(property)\s*([a-z0-9_]+)\s*\(((.|\n)*?\)?)\)$/m', $doc, $match)){
			foreach($match[3] as $key => $ini_string){
				$ini_string = preg_replace('/,\s/m', "\n", $ini_string);
				$match[3][$key] = parse_ini_string($ini_string);
				$this->properties[$match[2][$key]] = new Property($match[2][$key], parse_ini_string($ini_string) );
			}
		}
	}

	public function getXML(\DOMDocument $xml){
		$metadata = $xml->createElement('metadata');
		$metadata->setAttribute('class', $this->getShortName());
		foreach($this->getMetadataProperties() as $key => $val){
			$key = str_replace('_', '-', $key);
			$element = $metadata->appendChild($xml->createElement($key));
			foreach($val->getValues() as $vkey => $vval){
				$vkey = str_replace('_', '-', $vkey);
				if(!empty($vval)){
					$element->setAttribute($vkey, $vval);
				}
			}
		}
		return $metadata;
	}

	public function getMetadataProperties(){
		return $this->properties;
	}

	/**
	 * @param $name
	 *
	 * @return Property
	 * @throws \Corelib\Base\ObjectRelationalMapping\Exception
	 */
	public function getMetadataProperty($name){
		if(!$this->hasMetadataProperty($name)){
			throw new Exception('Property doesn\'t exists');
		} else {
			return $this->properties[$name];
		}
	}

	public function hasMetadataProperty($name){
		assert('is_string($name)');
		return isset($this->properties[$name]);
	}

	public function searchMetadataProperties($key, $value){
		$hash = md5($key.$value);
		if(!isset($this->search[$hash])){
			$this->search[$hash] = array();

			foreach($this->properties as $pkey => $property){
				if($value = $property->getValue($key)){
					$this->search[$hash][$pkey] = $property;
				}
			}

		}
		return $this->search[$hash];
	}
}
?>