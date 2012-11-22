<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorPhone validator class.
 *
 * Use this class to validate content against phone validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class Phone extends Regex {


	//*****************************************************************//
	//************** InputValidatorPhone class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $item
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^\+?[\-\s0-9]{8,}$/');
	}
}
?>