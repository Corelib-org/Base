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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package corelib
 * @subpackage Base
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Base.php 5066 2009-09-24 09:32:09Z wayland $)
 * @filesource
 */


if(!defined('I18N_COOKIE_NAME')){
	define('I18N_COOKIE_NAME', 'i18n');
}
if(!defined('I18N_COOKIE_TIMEOUT')){
	define('I18N_COOKIE_TIMEOUT', 31536000);
}
if(!defined('I18N_COOKIE_PATH')){
	define('I18N_COOKIE_PATH', '/');
}
if(!defined('I18N_COOKIE_PATH')){
	define('I18N_COOKIE_PATH', '/');
}
if(!defined('I18N_LANGUAGE_BASE')){
	define('I18N_LANGUAGE_BASE', 'share/lang/');
}
if(!defined('I18N_DEFAULT_TIMEZONE')){
	define('I18N_DEFAULT_TIMEZONE', date('e'));
}

class i18n implements Singleton,Output {
	/**
	 * @var i18n
	 */
	private static $instance = null;

	private $languages = array();
	private $date_formats = array();
	private $date_default_format = '%D %T';

	private $timezone = null;
	private $timezone_offset = 0;

	private $current_locale = null;
	private $current_language = null;

	private $default_language = null;

	private $language_files = array();

	protected $cookie_name = null;

	/**
	 *	@return i18n
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new i18n();
		}
		return self::$instance;
	}

	protected function __construct(){
		if(is_null($this->cookie_name)){
			$this->cookie_name = I18N_COOKIE_NAME;
		}
		ini_set('date.timezone', I18N_DEFAULT_TIMEZONE);
		date_default_timezone_set(I18N_DEFAULT_TIMEZONE);
		if(!isset($_COOKIE[$this->cookie_name.'_timezone'])){
			$this->setTimezone(I18N_DEFAULT_TIMEZONE);
		} else {
			$this->setTimezone($_COOKIE[$this->cookie_name.'_timezone']);
		}
	}

	/**
	 * Add new locale
	 *
	 * @param $language string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 * @param $locale string RFC 1766 valid locale string
	 * @return boolean true on success, else return false
	 */
	public function addLanguage($language, $locale){
		if(sizeof($this->languages) > 0){
			$this->current_language = $language;
			$this->current_locale = $locale;
			$this->default_language = $language;
		}
		$this->languages[$language] = $locale;
	}

	public function addDateFormat($language, $id, $format='%D %T'){
		$this->date_formats[$language][$id] = $format;
	}

	public function getDateFormat($id){
	//	echo '<pre style="text-align: left">';
	//	print_r($this);
	//	echo I18N_DEFAULT_TIMEZONE;
		if(isset($this->date_formats[$this->getLanguage()][$id])){
			return new i18nDateConverter($this->date_formats[$this->getLanguage()][$id], $this->getTimezoneOffset());
		} else {
			return new i18nDateConverter($this->date_default_format, $this->getTimezoneOffset());
		}
	}

	public function addLanguageFile($filename){
		assert('is_string($filename)');
		$this->addLanguageFilePath(I18N_LANGUAGE_BASE.$this->getLanguage().'/'.$filename);
	}

	public function addLanguageFilePath($filename){
		assert('is_string($filename) && is_file($filename)');
		$this->language_files[] = $filename;
	}

	/**
	 * Add a language file based on the full file path.
	 *
	 * This function is an alias of {@link i18n::addLanguageFilePath()}
	 *
	 * @see i18n::addLanguageFilePath()
	 * @deprecated use i18n::addLanguageFilePath() instead
	 * @uses i18n::addLanguageFilePath()
	 * @param string $filename language file full filename and path
	 * @return mixed same as {@link i18n::addLanguageFilePath()}
	 */
	public function addLangaugeFilePath($filename){

		return $this->addLanguageFilePath($filename);
	}

	public function getLanguage(){
		return $this->current_language;
	}
	public function getTimezone(){
		return $this->timezone;
	}
	public function getTimezoneOffset(){
		return $this->timezone_offset;
	}
	public function getLocale(){
		return $this->current_locale;
	}
	public function getDefaultLanguage(){
		return $this->default_language;
	}
	public function getFileBase(){
		return I18N_LANGUAGE_BASE.$this->getLanguage();
	}

	public function setLanguage($language){
		if(isset($this->languages[$language])){
			$this->current_locale = $this->languages[$language];
			$this->current_language = $language;
			setcookie($this->cookie_name, $language, time()+I18N_COOKIE_TIMEOUT, I18N_COOKIE_PATH);
			return true;
		} else {
			return false;
		}
	}

	public function setTimezone($timezone){
		$this->timezone = $timezone;
		setcookie($this->cookie_name.'_timezone', $timezone, time()+I18N_COOKIE_TIMEOUT, I18N_COOKIE_PATH);

		// Calculate time offset in seconds
		//echo I18N_DEFAULT_TIMEZONE;
		$default_timezone = timezone_open(I18N_DEFAULT_TIMEZONE);
		// echo $timezone;
		if(!$timezone = @timezone_open($timezone)){
			$timezone = $default_timezone;
		}
		$date = date_create(null, $default_timezone);
		//echo date('r', $default_timezone->getOffset($date))."\n";
		//echo date('r', $timezone->getOffset($date)),"\n";
		$this->timezone_offset = $default_timezone->getOffset($date) - $timezone->getOffset($date);
	}

	public function detectLanguage(){
		if(!isset($_COOKIE[$this->cookie_name]) || !isset($this->languages[$_COOKIE[$this->cookie_name]])){
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
				$locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				while (list(,$val) = each($locales)){
					if(strstr($val, ';')){
						list($language, $priority) = explode(';', $val, 2);
					} else {
						$language = $val;
						$priority = 1;
					}
					$languages[$language] = (float) str_replace('q=', '', $priority);
				}
				asort($languages);
				$languages = array_keys($languages);
				while(sizeof($languages) > 0){
					$language = array_pop($languages);
					if(isset($this->languages[$language])){
						$this->setLanguage($language);
						break;
					} else if(strstr($language, '-')){
						list($suffix,) = explode('-', $language);
						if(isset($this->languages[$suffix])){
							$this->setLanguage($suffix);
							break;
						}
					}
				}
			} else {
				$this->setLanguage($this->getDefaultLanguage());
			}
		} else {
			$this->setLanguage($_COOKIE[$this->cookie_name]);
		}
		return true;
	}

	public function getXML(DOMDocument $xml){
		$language = $xml->createElement('language');
		$language->setAttribute('language', $this->getLanguage());
		$language->setAttribute('locale', $this->getLocale());
		$language->setAttribute('timezone', $this->getTimezone());

		while (list(,$val) = each($this->language_files)){
			try {
				if(!is_file($val)){
					$lfile = str_replace('/'.$this->current_language.'/', '/'.$this->default_language.'/', $val);
				}
				if(isset($lfile) && !is_file($lfile)){
					throw new BaseException('Unable to load fallback language file '.$lfile.'. File does not excist', E_USER_ERROR);
				} else if(!isset($lfile)){
					$lfile = $val;
				}
				$languagefile = new DOMDocument('1.0', 'UTF-8');
				$languagefile->load($lfile);
				for ($i = 0; $item = $languagefile->documentElement->childNodes->item($i); $i++){
					if($item->nodeName == 'item'){
						$language->appendChild($xml->importNode($item, true));
					}
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
			unset($lfile);
		}
		reset($this->language_files);
		return $language;
	}
}
?>