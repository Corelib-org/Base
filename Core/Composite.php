<?php
namespace Corelib\Base\Core;

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
			throw new BaseException('Class is not a composite: '.get_class($this));
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
?>