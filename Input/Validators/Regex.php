<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorRegex validator class.
 *
 * Use this class to validate content based on a perl compatible regular expression.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class Regex implements Validator {


	//*****************************************************************//
	//************ InputValidatorRegex class properties ***************//
	//*****************************************************************//
	/**
	 * @var string perl compatibel regular expression
	 * @internal
	 */
	private $expr;


	//*****************************************************************//
	//************** InputValidatorRegex class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $expr perl compatibel regular expression
	 * @return void
	 */
	public function __construct($expr){
		$this->expr = $expr;
	}

	/**
	 * Validate content against regular expression.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		return preg_match($this->expr, $content);
	}
}
?>