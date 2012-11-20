<?php
namespace Corelib\Base\PageFactory;

/**
 * Composite output abstract class.
 *
 * @see Composite
 * @category corelib
 * @package Base
 * @since Version 5.0
 */
abstract class CompositeOutput extends \Composite implements Output {

	/**
	 * Add XML from components as child nodes.
	 *
	 * @param DOMElement $DOMnode
	 * @param array $components
	 * @return boolean true on success, else return false
	 */
	public function getComponentsXML(\DOMElement $DOMnode, array $components=null){
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
	public function addComponent(\Composite $component, $reference=null){
		assert('$component instanceof CompositeOutput');
		return parent::addComponent($component, $reference);
	}

}
