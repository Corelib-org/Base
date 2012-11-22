<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorIsFloat validator class.
 *
 * Use this class to validate content against float validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class IsFloat extends Regex {


	//*****************************************************************//
	//************ InputValidatorIsFloat class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^[0-9]+(\.[0-9]+)?$/');
	}
}
