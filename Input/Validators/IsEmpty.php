<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorEmpty validator class.
 *
 * Use this class to validate content against empty validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class IsEmpty implements Validator {


	//*****************************************************************//
	//************** InputValidatorEmpty class methods ****************//
	//*****************************************************************//
	/**
	 * Validate content against empty validation rules.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		return empty($content);
	}
}