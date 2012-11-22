<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorInteger validator class.
 *
 * Use this class to validate content against integer validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class IsInteger extends Regex {


	//*****************************************************************//
	//************ InputValidatorInteger class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^-?[0-9]+$/');
	}
}