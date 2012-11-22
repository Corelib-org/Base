<?php
namespace Corelib\Base\Input;
/**
 * InputValidator interface.
 *
 * Impliment this interface in order to create a new input validator
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
interface Validator {
	/**
	 * Validate content.
	 *
	 * @param mixed $content
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content);
}


?>