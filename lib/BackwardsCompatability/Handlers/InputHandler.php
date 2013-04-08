<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Input handler Classes
 *
 * <i>No Description</i>
 *
 * This script is part of the corelib project. The corelib project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.1.0 ($Id$)
 */
// use Corelib\Base\Converters\Converter, Corelib\Base\PageFactory\Output;
// use Corelib\Base\Exception;

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
/**
 * Input handler invalid variable error code.
 *
 * @var integer
 */
// define('INPUT_HANDLER_INVALID_VARIABLE', 1);


//*****************************************************************//
//********************** BaseException class **********************//
//*****************************************************************//
/**
 * Inputhandler exception class.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
// class BaseInputHandlerException extends Exception { }


//*****************************************************************//
//********** BaseInputHandlerInvalidGetException class ************//
//*****************************************************************//
/**
 * Inputhandler invalid get variable exception class.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
// class BaseInputHandlerInvalidGetException extends BaseInputHandlerException {


	//*****************************************************************//
	//****** BaseInputHandlerInvalidGetException class methods ********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 * @return void
	 */
	/*
	public function __construct($message = null, $code = INPUT_HANDLER_INVALID_VARIABLE, Exception $previous = null){
		parent::__construct('Ivalid get variable: '.$message, $code, $previous);
	}
}
*/

//*****************************************************************//
//********* BaseInputHandlerInvalidPostException class ************//
//*****************************************************************//
/**
 * Inputhandler invalid post variable exception class.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
//class BaseInputHandlerInvalidPostException extends BaseInputHandlerException {


	//*****************************************************************//
	//****** BaseInputHandlerInvalidPostException class methods *******//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $message
	 * @param integer $code
	 * @param Exception $previous
	 * @return void
	 */
/*
	public function __construct($message = null, $code = INPUT_HANDLER_INVALID_VARIABLE, Exception $previous = null){
		parent::__construct('Ivalid post variable: '.$message, $code, $previous);
	}

}

*/
//*****************************************************************//
//********************** InputHandler class ***********************//
//*****************************************************************//


//*****************************************************************//
//****************** InputValidator interface *********************//
//*****************************************************************//


//*****************************************************************//
//***************** InputValidatorRegex class *********************//
//*****************************************************************//



//*****************************************************************//
//***************** InputValidatorEmail class *********************//
//*****************************************************************//
/**
 * InputValidatorEmail validator class.
 *
 * Use this class to validate content against email validation rules.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class InputValidatorEmail extends InputValidatorRegex {


//*****************************************************************//
//************* InputValidatorEmail class methods *****************//
//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		parent::__construct('/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|mobi|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i');
	}
}

//*****************************************************************//
//****************** InputValidatorURL class **********************//
//*****************************************************************//


//*****************************************************************//
//***************** InputValidatorInteger class *******************//
//*****************************************************************//



//*****************************************************************//
//***************** InputValidatorIsFloat class *******************//
//*****************************************************************//


//*****************************************************************//
//******************* InputValidatorEnum class ********************//
//*****************************************************************//



//*****************************************************************//
//**************** InputValidatorNotEmpty class *******************//
//*****************************************************************//



//*****************************************************************//
//******************* InputValidatorEmpty class *******************//
//*****************************************************************//



//*****************************************************************//
//******************* InputValidatorPhone class *******************//
//*****************************************************************//


//*****************************************************************//
//****************** InputValidatorEquals class *******************//
//*****************************************************************//



//*****************************************************************//
//******************* InputValidatorArray class *******************//
//*****************************************************************//



//*****************************************************************//
//******************* InputValidatorIsSet class *******************//
//*****************************************************************//



//*****************************************************************//
//*************** InputValidatorModelExists class *****************//
//*****************************************************************//

//*****************************************************************//
//******************* InputValidatorXpath class *******************//
//*****************************************************************//

?>