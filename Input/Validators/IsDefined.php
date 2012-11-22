<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorIsSet validator class.
 *
 * Use this class to validate content against isset validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class IsDefined extends Regex {


	//*****************************************************************//
	//************* InputValidatorIsSet class methods *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $item
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^.*$/s');
	}
}
?>