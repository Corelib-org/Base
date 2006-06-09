<?php
interface Singleton {
	public static function &getInstance();
}

interface ObserverSubject {
	public function registerObserver(Observer &$observer);
	public function removeObserver(Observer &$observer);
	public function notifyObservers();
}

interface Observer {
	public function register(ObserverSubject &$subject);
	public function update($update);
}

interface Converter {
	public function getXML(DOMDocument $xml);
	public function convert($data);
}
?>