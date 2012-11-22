<?php
namespace Corelib\Base\Input\Validators;

/**
 * InputValidatorEnum validator class.
 *
 * Use this class to validate content against enum validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class Enum extends Regex {


	//*****************************************************************//
	//************** InputValidatorEnum class methods *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * The constructor takes one or more parameters as valid enum values.
	 *
	 * @param string $item
	 * @return void
	 */
	public function __construct($item=null /*, [$items...] */){
		$args = func_get_args();
		parent::__construct('/^('.implode('|', $args).')$/');
	}
}
?>