<?php
namespace Corelib\Base\i18n\Validators;
use Corelib\Base\Input\Validator;

/**
 * Validate timezone against known timezones.
 *
 * uses timezone_identifiers_list() to see if timezone is valid
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class Timezone implements Validator {

	public function validate($content){
		return in_array($content, timezone_identifiers_list());
	}

}