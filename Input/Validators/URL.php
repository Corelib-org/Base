<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorURL validator class.
 *
 * Use this class to validate content against url validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class URL extends Regex {


	//*****************************************************************//
	//************** InputValidatorURL class methods ******************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^(http|https|ftp):\/\/[a-z0-9\/:_\-_\.\?\$,~=#&%\+]+$/i');
	}
}
