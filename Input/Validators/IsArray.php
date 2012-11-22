<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorArray validator class.
 *
 * Use this class to validate content against array validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class IsArray implements Validator {


	//*****************************************************************//
	//************* InputValidatorArray class properties **************//
	//*****************************************************************//
	/**
	 * @var InputValidator
	 * @internal
	 */
	private $validator;


	//*****************************************************************//
	//************** InputValidatorArray class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance
	 *
	 * The optional parameter $validator can be specified in order to
	 * check all array values against the same validator.
	 *
	 * @param InputValidator $validator optional validator
	 * @return void
	 */
	public function __construct(InputValidator $validator=null){
		$this->validator = $validator;
	}

	/**
	 * Validate content against array validation rules.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		if(!$this->validator instanceof InputValidator && is_array($content)){
			return true;
		} else {
			foreach ($content as $k => $v) {
				if(!$this->validator->validate($content[$k])) {
					return false;
				}
			}
			return true;
		}
	}
}
?>