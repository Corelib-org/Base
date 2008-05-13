<?php
interface Singleton {
	public static function getInstance();
}

interface ObserverSubject {
	public function registerObserver(Observer $observer);
	public function removeObserver(Observer $observer);
	public function notifyObservers();
}

interface Observer {
	public function register(ObserverSubject $subject);
	public function update($update);
}

interface Converter {
	public function convert($data);
}


interface Output {
	public function getXML(DOMDocument $xml);
	public function &getArray();
}

abstract class Decorator {
	protected $decorator = null;
		
	public function getDecorator(){
		return $this->decorator;
	}
	
	protected function buildXML(DOMDocument $xml, DOMElement $DOMNode){
		if(!is_null($this->decorator)){
			$DOMElement = $this->decorator->getXML($xml);
			$DOMElement->appendChild($DOMNode);
			return $DOMElement;
		} else {
			return $DOMNode;
		}
	}
}

abstract class Component {
	/**
	 * Child UserComponents
	 * 
	 * @var Array instantiated components
	 */
	protected $components = array();
	
	public function getComponentsXML(DOMDocument $xml, DOMElement $DOMnode){
		while(list(,$val) = each($this->components)){
			$DOMnode->appendChild($val->getXML($xml));
		}
		reset($this->components);
	}
	
	public function removeComponents(){
		$this->components = array();
		return true;
	}
	
}
?>