<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Internationalization Functions and Classes.
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
 * @subpackage i18n
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2010
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 2.0.0 ($Id: Base.php 5066 2009-09-24 09:32:09Z wayland $)
 */
use Corelib\Base\Converters\Converter,
	Corelib\Base\PageFactory\Output;
use Corelib\Base\Input\Validator;
use Corelib\Base\Event\Action, Corelib\Base\Event\Event;

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('I18N_COOKIE_NAME')){
	/**
	 * i18n Cookie name.
	 *
	 * @var string
	 */
	define('I18N_COOKIE_NAME', 'i18n');
}
if(!defined('I18N_COOKIE_TIMEOUT')){
	/**
	 * i18n cookie lifetime.
	 *
	 * @var integer seconds
	 */
	define('I18N_COOKIE_TIMEOUT', 31536000);
}
if(!defined('I18N_COOKIE_PATH')){
	/**
	 * i18n Cookie path.
	 *
	 * @var string
	 */
	define('I18N_COOKIE_PATH', '/');
}
if(!defined('I18N_LANGUAGE_BASE')){
	/**
	 * i18n language file base.
	 *
	 * Directory where the language files are located.
	 *
	 * @var string
	 */
	define('I18N_LANGUAGE_BASE', 'share/lang/');
}
if(!defined('I18N_DEFAULT_TIMEZONE')){
	/**
	 * i18n Default timezone.
	 *
	 * @var string
	 */
	define('I18N_DEFAULT_TIMEZONE', date('e'));
}




//*****************************************************************//
//********************** i18nTimezones class **********************//
//*****************************************************************//



//*****************************************************************//
//**************** i18nEventTimezoneChange class ******************//
//*****************************************************************//



//*****************************************************************//
//************ i18nDetectLanguageEventActions class ***************//
//*****************************************************************//


//*****************************************************************//
//**************** i18nEventTimezoneChange class ******************//
//*****************************************************************//



?>