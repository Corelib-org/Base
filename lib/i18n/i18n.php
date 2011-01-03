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
//*************************** i18n class **************************//
//*****************************************************************//
/**
 * i18n class.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class i18n implements Singleton,Output {


	//*****************************************************************//
	//********************* i18n class properties *********************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var InputHandler
	 * @internal
	 */
	private static $instance = null;

	/**
	 * @var array list of known locales.
	 * @internal
	 */
	private $locales = array();

	/**
	 * @var i18nLocale
	 * @internal
	 */
	private $locale = null;

	/**
	 * @var i18nLocale
	 * @internal
	 */
	private $fallback = null;

	/**
	 * @var string
	 * @internal
	 */
	private $timezone = null;

	/**
	 * @var array language files
	 * @internal
	 */
	private $language_files = array();

	/**
	 * @var string cookie name
	 * @internal
	 */
	private $cookie_name = null;

	/**
	 * @var string cookie path
	 * @internal
	 */
	private $cookie_path = null;

	/**
	 * @var string cookie timeout
	 * @internal
	 */
	private $cookie_timeout = null;


	//*****************************************************************//
	//*********************** i18n class methods **********************//
	//*****************************************************************//
	/**
	 * i18n constructor.
	 *
	 * @return void
	 * @internal
	 */
	protected function __construct($cookie_name=I18N_COOKIE_NAME, $cookie_path=I18N_COOKIE_PATH, $cookie_timeout=I18N_COOKIE_TIMEOUT){
		$this->cookie_name = $cookie_name;
		$this->cookie_path = $cookie_path;
		$this->cookie_timeout = $cookie_timeout;
		$this->getTimezone();
	}

	/**
	 * 	Return instance of i18n.
	 *
	 * 	Please refer to the {@link Singleton} interface for complete
	 * 	description.
	 *
	 * 	@see Singleton
	 *  @uses i18n::$instance
	 *	@return i18n
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new i18n();
			EventHandler::getInstance()->register(new i18nDetectLanguageEventActions(), 'EventRequestStart');
			EventHandler::getInstance()->register(new i18nApplyDefaultSettingsEventActions(), 'EventApplyDefaultSettings');
		}
		return self::$instance;
	}

	/**
	 * Add locale.
	 *
	 * @param i18nLocale $locale
	 * @return i18nLocale
	 */
	public function addLocale(i18nLocale $locale){
		if(sizeof($this->locales) > 0){
			$this->fallback = $locale;
		}
		$this->locales[$locale->getLanguage()] = $locale;
		return $locale;
	}

	/**
	 * Add language file.
	 *
	 * @uses i18n::addLanguageFilePath()
	 * @param string $filename
	 * @return boolean true on success, else return false
	 */
	public function addLanguageFile($filename){
		assert('is_string($filename)');
		return $this->addLanguageFilePath(I18N_LANGUAGE_BASE.$this->locale->getLanguage().'/'.$filename);
	}

	/**
	 * Add a language file based on the full file path.
	 *
	 * @param string $filename language file full filename and path
	 * @return boolean true on success, else return false
	 */
	public function addLanguageFilePath($filename){
		assert('is_string($filename)');
		if(is_file($filename)){
			$this->language_files[] = $filename;
			return true;
		} else {
			trigger_error('Unable to add language file, no such file or directory: '.$filename, E_USER_WARNING);
			return false;
		}
	}

	/**
	 * Set current locale.
	 *
	 * @param $language string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 * @return boolean true if locale exists, else return false
	 */
	public function setLocale($language){
		if(isset($this->locales[$language])){
			$this->locale = $this->locales[$language];
			setlocale(LC_TIME, $this->locale->getLocale());
			setlocale(LC_COLLATE, $this->locale->getLocale());
			setlocale(LC_CTYPE, $this->locale->getLocale());
			setlocale(LC_MONETARY, $this->locale->getLocale());
			setcookie($this->cookie_name, $language, time()+I18N_COOKIE_TIMEOUT, I18N_COOKIE_PATH);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get current locale.
	 *
	 * @return i18nLocale if current locale is set, else return false
	 */
	public function getLocale(){
		if($this->locale instanceof i18nLocale){
			return $this->locale;
		} else if(!is_null($this->fallback)){
			return $this->fallback;
		} else {
			return false;
		}
	}

	public function getDateConverter($ident){
		if($format  = $this->getLocale()->getDateFormat($ident)){
			return new DateConverter($format);
		} else {
			return false;
		}
	}

	/**
	 * Set current timezone.
	 *
	 * @param string $timezone Valid timezone identifier
	 * @return boolean true on success, else return false.
	 */
	public function setTimezone($timezone){
		$this->timezone = $timezone;
		ini_set('date.timezone', $this->timezone);
		date_default_timezone_set($this->timezone);
		setcookie($this->cookie_name.'_timezone', $timezone, time()+I18N_COOKIE_TIMEOUT, I18N_COOKIE_PATH);
		EventHandler::getInstance()->trigger(new i18nEventTimezoneChange($this->timezone));
		return true;
	}

	/**
	 * Get current timezone.
	 *
	 * @return string current timezone
	 */
	public function getTimezone(){
		if(is_null($this->timezone)){
			if(!isset($_COOKIE[$this->cookie_name.'_timezone'])){
				$this->setTimezone(I18N_DEFAULT_TIMEZONE);
			} else {
				$this->setTimezone($_COOKIE[$this->cookie_name.'_timezone']);
			}
		}
		return $this->timezone;
	}

	/**
	 * Detect client language.
	 *
	 * Detect the client language and fallback to default
	 * language if unable to detect language.
	 *
	 * @return string
	 */
	public function detectLanguage(){
		if(!isset($_COOKIE[$this->cookie_name]) || !isset($this->locales[$_COOKIE[$this->cookie_name]])){
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
					if(isset($this->locales[$language])){
						$this->setLocale($language);
						break;
					} else if(strstr($language, '-')){
						list($suffix,) = explode('-', $language);
						if(isset($this->languages[$suffix])){
							$this->setLocale($suffix);
							break;
						}
					}
				}
			} else if($this->fallback){
				$this->setLocale($this->fallback->getLanguage());
			} else {
				throw new BaseException('No fallback locale found, please add at least one locale before using i18n classes.', E_USER_ERROR);
			}
		} else {
			$this->setLocale($_COOKIE[$this->cookie_name]);
		}
		return true;
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		$i18n = $xml->createElement('i18n');
		$i18n->setAttribute('language', $this->getLocale()->getLanguage());
		$i18n->setAttribute('locale', $this->getLocale()->getLocale());
		$i18n->setAttribute('timezone', $this->getTimezone());

		while (list(,$val) = each($this->language_files)){
			if(!is_file($val)){
				$lfile = str_replace('/'.$this->locale->getLanguage().'/', '/'.$this->fallback->getLanguage().'/', $val);
			}
			if(isset($lfile) && !is_file($lfile)){
				trigger_errorn('Unable to load fallback language file '.$lfile.'. File does not excist', E_USER_WARNING);
			} else if(!isset($lfile)){
				$lfile = $val;
			}
			$languagefile = new DOMDocument('1.0', 'UTF-8');
			$languagefile->load($lfile);
			for ($i = 0; $item = $languagefile->documentElement->childNodes->item($i); $i++){
				if($item->nodeName == 'item'){
					$i18n->appendChild($xml->importNode($item, true));
				}
			}
			unset($lfile);
		}
		reset($this->language_files);
		return $i18n;
	}
}


//*****************************************************************//
//********************** i18nTimezones class **********************//
//*****************************************************************//
/**
 * i18nTimezones class.
 *
 * This class can be used to retrieve all available timezones
 * on the current system.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class i18nTimezones implements Output {


	//*****************************************************************//
	//******************* i18nTimezones propeties *********************//
	//*****************************************************************//
	/**
	 * List of system timezones.
	 *
	 * @var array timezones
	 * @internal
	 */
	private $timezones = null;


	//*****************************************************************//
	//******************** i18nTimezones methods **********************//
	//*****************************************************************//
	/**
	 * i18nTimezones constructor.
	 *
	 * @param array $timezones custom list of timeszones
	 * @return void
	 */
	public function __construct($timezones=null){
		 $this->timezones = $timezones;
	}

	/**
	 * Get XML Output.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 */
	public function getXML(DOMDocument $xml){
		if(is_null($this->timezones)){
			$this->timezones = timezone_identifiers_list();
		}
		$timezones = $xml->createElement('i18n-timezones');
		foreach($this->timezones as $key => $val) {
			if(is_array($val)){
				$key = $val[0];
				$val = $val[1];
			}
			$timezone = $timezones->appendChild($xml->createElement('timezone', $val));
			if(!is_numeric($key)){
				$timezone->setAttribute('name', $key);
			} else {
				$timezone->setAttribute('name', $val);
			}
		}
		reset($this->timezones);
		return $timezones;
	}
}


//*****************************************************************//
//**************** i18nEventTimezoneChange class ******************//
//*****************************************************************//
/**
 * i18nEventTimezoneChange class.
 *
 * This class can be used to retrieve all available timezones
 * on the current system.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class i18nEventTimezoneChange implements Event {


	//*****************************************************************//
	//********* i18nEventTimezoneChange class properties **************//
	//*****************************************************************//
	/**
	 * Current timezone.
	 *
	 * @var string
	 * @internal
	 */
	private $timezone = null;

	//*****************************************************************//
	//*********** i18nEventTimezoneChange class methods ***************//
	//*****************************************************************//
	/**
	 * i18nEventTimezoneChange constructor.
	 *
	 * @param array $timezone
	 * @return void
	 */
	public function __construct($timezone){
		$this->timezone = $timezone;
	}

	/**
	 * Get current timezone.
	 *
	 * @return string
	 */
	public function getTimezone(){
		return $this->timezone;
	}
}


//*****************************************************************//
//************ i18nDetectLanguageEventActions class ***************//
//*****************************************************************//
/**
 * i18nDetectLanguageEventActions class.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class i18nDetectLanguageEventActions extends EventAction {


	//*****************************************************************//
	//******** i18nDetectLanguageEventActions class methods ***********//
	//*****************************************************************//
	/**
	 * Update method.
	 *
	 * @see EventAction::update()
	 */
	public function update(Event $event){
		i18n::getInstance()->detectLanguage();
	}
}


//*****************************************************************//
//**************** i18nEventTimezoneChange class ******************//
//*****************************************************************//
/**
 * i18nEventTimezoneChange class.
 *
 * This class can be used to retrieve all available timezones
 * on the current system.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class i18nApplyDefaultSettingsEventActions extends EventAction {


	//*****************************************************************//
	//***** i18nApplyDefaultSettingsEventActions class methods ********//
	//*****************************************************************//
	/**
	 * Update method.
	 *
	 * @see EventAction::update()
	 */
	public function update(Event $event){
		$event->getPage()->addSettings(i18n::getInstance());
	}
}

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
class i18nTimezoneValidator implements InputValidator {

	public function validate($content){
		return in_array($content, timezone_identifiers_list());
	}

}
?>