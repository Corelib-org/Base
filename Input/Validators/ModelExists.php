<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorModelExists validator class.
 *
 * Use this class to validate content against a class and a read() method.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class ModelExists implements Validator {


	//*****************************************************************//
	//********** InputValidatorModelExists class properties ***********//
	//*****************************************************************//
	/**
	 * @var string class name to match with
	 * @internal
	 */
	private $class = null;

	/**
	 * @var string Read callback
	 * @internal
	 */
	private $callback = null;

	/**
	 * @var object instance of {@link InputValidatorModelExists::$class} class name.
	 * @internal
	 */
	private $instance = false;


	//*****************************************************************//
	//********** InputValidatorModelExists class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $class class name
	 * @param string $callback callback method name
	 * @return void
	 */
	public function __construct($class, $callback='getById'){
		$this->class = $class;
		$this->callback = $callback;
	}

	/**
	 * Validate content against class read validation rules.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		// $this->instance = new $this->class($content);
		if(is_null($this->callback)){
			$this->instance = new $this->class($content);
			return $this->instance->read();
		} else {
			if(method_exists($this->class, $this->callback)){
				if(call_user_func_array(array($this->class, $this->callback), func_get_args())){
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Get instance of object used to check against.
	 *
	 * @return mixed instance of object if {@link InputValidatorModelExists::validate()} returned true, else return false
	 */
	public function getInstance(){
		return $this->instance;
	}
}
?>