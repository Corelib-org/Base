<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base Interfaces and abstract classes.
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 1.1.0 ($Id$)
 * @filesource
 * @todo Output interface is used by PageFactory and shouldbe moved there
 * @todo Decorator and Component pattern is outdated and should be removed or rewritten
 */

//*****************************************************************//
//********************** Singleton interface **********************//
//*****************************************************************//
/**
 * Singleton interface
 *
 * Use this interface for defining the base of all Singleton
 * classes, however since PHP does not allow us to to include
 * the specifics of the constructer and __close, you have to
 * remember to set the constructor and __close private to get
 * the desired effect of a singleton class.
 *
 * @see http://en.wikipedia.org/wiki/Singleton_pattern
 */
interface Singleton {
	/**
	 * @return Object Unique version of the object instance
	 */
	public static function getInstance();
}


/**
 * @see http://en.wikipedia.org/wiki/Composite_pattern
 */
abstract class Composite {

	private $parent = false;

	protected $components = array();

	/**
	 * @return Composite
	 */
	public function getComposite(){
		return false;
	}

	private function _setParent(Composite $parent){
		$this->parent = $parent;
	}

	public function addComponent(Composite $component){
		if($composite = $this->getComposite()){
			$uuid = RFC4122::generate();
			$component->_setParent($composite);
			$this->components[$uuid] = $component;
			return $uuid;
		} else {
			throw new Exception('Not allowed here');
		}
	}

	public function removeComponent($uuid){
		if($composite = $this->getComposite()){
			if(isset($this->components[$uuid])){
				unset($this->components[$uuid]);
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception('Not allowed here');
		}
	}

	public function getComponent($uuid){
		if($composite = $this->getComposite()){
			if(isset($this->components[$uuid])){
				return $this->components[$uuid];
			} else {
				return false;
			}
		} else {
			throw new Exception('Not allowed here');
		}
	}

	public function getParent(){
		return $this->parent;
	}
}


abstract class CompositeOutput extends Composite implements Output {
	public function getComponentsXML(array $components, DOMElement $DOMnode){
		foreach($components as $component){
			$DOMnode->appendChild($component->getXML($DOMnode->ownerDocument));
		}
		reset($this->components);
	}

	public function addComponent(CompositeOutput $component){
		parent::addComponent($component);
	}

}






interface Output {
	public function getXML(DOMDocument $xml);
}


/**
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 */
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

/**
 * @see http://en.wikipedia.org/wiki/Composite_pattern
 */
abstract class Component {
	/**
	 * Child Components
	 *
	 * @var Array instantiated components
	 */
	protected $components = array();

	/**
	 * Parent Component
	 *
	 * @var Component parent component
	 */
	protected $parent = null;


	public function getComponentsXML(DOMDocument $xml, DOMElement $DOMnode){
		while(list(,$val) = each($this->components)){
			$DOMnode->appendChild($val->getXML($xml));
		}
		reset($this->components);
	}

	public function getComponentsArray(array &$array){
		while(list(,$val) = each($this->components)){
			$array[] = $val->getArray();
		}
		reset($this->components);
	}

	public function removeComponents(){
		$this->components = array();
		return true;
	}

	public function addComponent(Component $component){
		$this->components[] = $component;
		$component->setParentComponent($this);
		return $component;
	}

	public function setParentComponent(Component $component){
		$this->parent = $component;
		return $component;
	}

	protected function _commitComponents($recursive=true){
		if($recursive){
			foreach ($this->components as $component){
				$component->commit();
			}
		}
	}

	public function commit($recursive=true){
		$this->_commitComponents($recursive);
	}
}
?>