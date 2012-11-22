<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorEquals validator class.
 *
 * Use this class to validate content against equals validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class Equals implements Validator {


	//*****************************************************************//
	//************ InputValidatorEquals class properties **************//
	//*****************************************************************//
	/**
	 * @var string
	 * @internal
	 */
	private $expr;


	//*****************************************************************//
	//************* InputValidatorEquals class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $expr string to match against.
	 * @return void
	 */
	public function __construct($expr){
		$this->expr = $expr;
	}

	/**
	 * Validate content against equals validation rules.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		return ($this->expr == $content);
	}
}
?>