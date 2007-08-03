<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 *	PageFactory Generic Output Class
 *
 *	<i>No Description</i>
 *
 *	LICENSE: This source file is subject to version 1.0 of the
 *	Bravura Distribution license that is available through the
 *	world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 *	If you did not receive a copy of the Bravura License and are
 *	unable to obtain it through the web, please send a note to
 *	license@bravura.dk so we can mail you a copy immediately.
 *
 *
 *	@author Steffen Sørensen <steffen@bravura.dk>
 *	@copyright Copyright (c) 2006 Bravura ApS
 * 	@license http://www.bravura.dk/licence/corelib_1_0/
 *	@package corelib
 *	@subpackage Base
 *	@link http://www.bravura.dk/
 *	@version 1.0.0 ($Id$)
 */

/**
 *
 */
class GenericOutput implements Output {
	/**
	 * @var DOMElement
	 */
	private $xml;
	/**
	 * @var Array
	 */
	private $array = array();

	/**
	 * @var string
	 */
	private $string;

	public function setArray(&$array){
		try {
			StrictTypes::isArray($array);
		} catch (BaseException $e){
			echo $e;
		}
		$this->array = &$array;
	}

	public function setXML(DOMElement $xml){
		$this->xml = $xml;
		return $this->xml;
	}

	public function setString($string){
		try {
			StrictTypes::isString($string);
		} catch (BaseException $e){
			echo $e;
		}
		$this->string = $string;
	}

	public function getXML(DOMDocument $xml){
		$this->xml = $xml->importNode($this->xml, true);
		return $this->xml;
	}
	public function &getArray(){
		return $this->array;
	}
	public function getString($format = '%1$s'){
		return $this->string;
	}
}