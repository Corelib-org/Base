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
 *
 * @category corelib
 * @package Base
 *
 * @link http://www.corelib.org/
 * @version 1.2.0 ($Id$)
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
 * the specifics of the constructer and __clone, you have to
 * remember to set the constructor and __clone private to get
 * the desired effect of a singleton class.
 *
 * @see http://en.wikipedia.org/wiki/Singleton_pattern
 * @category corelib
 * @package Base
 */
interface Singleton {


	//*****************************************************************//
	//***************** Singleton interface methods *******************//
	//*****************************************************************//
	/**
	 * @return Object Unique version of the object instance
	 */
	public static function getInstance();
}


//*****************************************************************//
//************************ Output interface ***********************//
//*****************************************************************//
/**
 * Output interface
 *
 * This is the blue print for output classes.
 *
 * @category corelib
 * @package Base
 */
interface Output {


	//*****************************************************************//
	//******************* Output interface methods ********************//
	//*****************************************************************//
	/**
	 * Get output XML.
	 *
	 * @param DOMDocument $xml
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml);
}


//*****************************************************************//
//************************ Composite class ************************//
//*****************************************************************//
/**
 * Composite abstract class.
 *
 * @see http://en.wikipedia.org/wiki/Composite_pattern
 * @category corelib
 * @package Base
 * @since Version 5.0
 */
abstract class Composite {


	//*****************************************************************//
	//***************** Composite class properties ********************//
	//*****************************************************************//
	/**
	 * Parent component.
	 *
	 * @var Composte
	 * @internal
	 */
	private $parent = false;

	/**
	 * @var array list of Components
	 * @internal
	 */
	protected $components = array();


	//*****************************************************************//
	//******************* Composite class methods *********************//
	//*****************************************************************//
	/**
	 * Get composite.
	 *
	 * Check to see if component is a composite and allows add Component.
	 * if it is a composite return instance of the object it self, else
	 * return false. this feature is best discribed in the book "Design patterns"
	 * from Addison and Wesley.
	 *
	 * @return Composite
	 */
	public function getComposite(){
		return false;
	}

	/**
	 * Set parent composite.
	 *
	 * @param Composite $parent
	 * @return void
	 * @internal
	 */
	private function _setParent(Composite $parent){
		$this->parent = $parent;
	}

	/**
	 * Add component.
	 *
	 * Add a component to composite. if {@link Composite::getComposite()}
	 * returns true the component is added else a exception is thrown.
	 *
	 * @param Composite $component
	 * @param string $reference
	 * @return string reference id, if $reference is null a uuid is returned
	 * @throws BaseException
	 */
	public function addComponent(Composite $component, $reference=null){
		if($composite = $this->getComposite()){
			if(is_null($reference)){
				$reference = RFC4122::generate();
			}
			$component->_setParent($composite);
			$this->components[$reference] = $component;
			return $reference;
		} else {
			throw new BaseException('Not allowed here');
			return false;
		}
	}

	/**
	 * Remove component.
	 *
	 * Remove a component from composite. if {@link Composite::getComposite()}
	 * returns true the component is removed else a exception is thrown.
	 *
	 * @param string $reference retrieved from {@link Composite::addComponent()}
	 * @return boolean true on success, else return false
	 * @throws BaseException
	 */
	public function removeComponent($reference){
		if($composite = $this->getComposite()){
			if(isset($this->components[$reference])){
				unset($this->components[$reference]);
				return true;
			} else {
				return false;
			}
		} else {
			throw new BaseException('Not allowed here');
			return false;
		}
	}

	/**
	 * Get component.
	 *
	 * Get a component from composite. if {@link Composite::getComposite()}
	 * returns true the component is returned else a exception is thrown.
	 *
	 * @param string $reference retrieved from {@link Composite::addComponent()}
	 * @return Composite on success, else return boolean false
	 * @throws BaseException
	 */
	public function getComponent($reference){
		if($composite = $this->getComposite()){
			if(isset($this->components[$reference])){
				return $this->components[$reference];
			} else {
				return false;
			}
		} else {
			throw new BaseException('Not allowed here');
			return false;
		}
	}

	/**
	 * Get parent composite.
	 *
	 * @return Composite on success, else return false.
	 */
	public function getParent(){
		return $this->parent;
	}
}

//*****************************************************************//
//************************ Composite class ************************//
//*****************************************************************//
/**
 * Composite output abstract class.
 *
 * @see Composite
 * @category corelib
 * @package Base
 * @since Version 5.0
 */
abstract class CompositeOutput extends Composite implements Output {

	/**
	 * Add XML from components as child nodes.
	 *
	 * @param DOMElement $DOMnode
	 * @param array $components
	 * @return boolean true on success, else return false
	 */
	public function getComponentsXML(DOMElement $DOMnode, array $components=null){
		if(is_null($components)){
			$components = $this->components;
		}
		foreach($components as $component){
			$DOMnode->appendChild($component->getXML($DOMnode->ownerDocument));
		}
		reset($this->components);
		return true;
	}

	/**
	 * Add component.
	 *
	 * @see Composite::addComponent()
	 */
	public function addComponent(Composite $component){
		assert("instanceof CompositeOutput");
		return parent::addComponent($component);
	}

}
?>