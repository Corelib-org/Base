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