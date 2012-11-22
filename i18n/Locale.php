<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Internationalization Locale.
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
 * @version 2.0.0 ($Id$)
 */
namespace Corelib\Base\i18n;

//*****************************************************************//
//************************ i18nLocale class ***********************//
//*****************************************************************//
/**
 * i18nLocale class.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class Locale {


	//*****************************************************************//
	//***************** i18nLocale class properties *******************//
	//*****************************************************************//
	/**
	 * @var string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 * @internal
	 */
	private $language = null;

	/**
	 * @var string RFC 1766 valid locale string
	 * @internal
	 */
	private $locale = null;

	/**
	 * @var array date formats
	 * @internal
	 */
	private $date_formats = array();

	/**
	 * @var array date input formats
	 * @internal
	 */
	private $date_input_formats = array();


	//*****************************************************************//
	//******************* i18nLocale class methods ********************//
	//*****************************************************************//
	/**
	 * Create new locale instance.
	 *
	 * @param $language string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 * @param $locale string RFC 1766 valid locale string
	 * @return void
	 */
	public function __construct($language, $locale){
		assert('is_string($language)');
		assert('is_string($locale)');

		$this->language = $language;
		$this->locale = $locale;
	}

	/**
	 * Add new date output format.
	 *
	 * @param string $ident identifying name
	 * @param string $format date format
	 * @return boolean true on succes, else return false
	 */
	public function addDateFormat($ident, $format){
		assert('is_string($ident)');
		assert('is_string($format)');

		$this->date_formats[$ident] = $format;
		return true;
	}

	/**
	 * Get date output format based on ident.
	 *
	 * @param string dateformat ident
	 * @see i18nLocale::addDateFormat()
	 * @return string if format was found, else return false
	 */
	public function getDateFormat($ident){
		assert('is_string($ident)');

		if(isset($this->date_formats[$ident])){
			return $this->date_formats[$ident];
		} else {
			return false;
		}
	}

	/**
	 * Add new date input format.
	 *
	 * @param string $ident identifying name
	 * @param string $format date format
	 * @param string $hint date format hint
	 * @return boolean true on succes, else return false
	 */
	public function addDateFormatInputFormat($ident, $format, $hint=null){
		assert('is_string($ident)');
		assert('is_string($format)');
		assert('(is_null($hint) || is_string($hint))');

		$this->date_input_formats[$ident] = array('format' => $format, 'hint' => $hint);
		return true;
	}

	/**
	 * Get language.
	 *
	 * @return string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 */
	public function getLanguage(){
		return $this->language;
	}

	/**
	 * Get Locale.
	 *
	 * @return string RFC 1766 valid locale string
	 */
	public function getLocale(){
		return $this->locale;
	}
}
?>