<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorNotEmpty validator class.
 *
 * Use this class to validate content against not empty validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class NotEmpty implements Validator {


	//*****************************************************************//
	//************ InputValidatorNotEmpty class methods ***************//
	//*****************************************************************//
	/**
	 * Validate content against not empty validation rules.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		return !empty($content);
	}
}
?>