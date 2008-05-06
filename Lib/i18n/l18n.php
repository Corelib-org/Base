<?php
if(!defined('L18N_COOKIE_NAME')){
	define('L18N_COOKIE_NAME', 'l18n');
}
if(!defined('L18N_COOKIE_TIMEOUT')){
	define('L18N_COOKIE_TIMEOUT', 31536000);
}
if(!defined('L18N_COOKIE_PATH')){
	define('L18N_COOKIE_PATH', '/');
}
if(!defined('L18N_COOKIE_PATH')){
	define('L18N_COOKIE_PATH', '/');
}
if(!defined('L18N_DEFAULT_TIMEZONE')){
	define('L18N_DEFAULT_TIMEZONE', date('e'));
}

class l18n implements Singleton,Output {
	/**
	 * @var l18n
	 */
	private static $instance = null;
	
	private $locales = array();
	private $date_formats = array();
	private $date_default_format = '%D %T';
	
	private $timezone = null;
	private $timezone_offset = 0;
	
	private $current_locale = null;
	private $current_language = null;
	
	/**
	 *	@return l18n
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new l18n();
		}
		return self::$instance;
	}
	
	private function __construct(){
		ini_set('date.timezone', L18N_DEFAULT_TIMEZONE);
		date_default_timezone_set(L18N_DEFAULT_TIMEZONE);
		if(!isset($_COOKIE[L18N_COOKIE_NAME.'_timezone'])){
			$this->setTimezone(L18N_DEFAULT_TIMEZONE);
		} else {
			$this->setTimezone($_COOKIE[L18N_COOKIE_NAME.'_timezone']);
		}
	}	
	
	/**
	 * Add new locale
	 * 
	 * @param $language string ISO-639 language abbreviation and any two-letter initial subtag defined by ISO-3166
	 * @param $locale string RFC 1766 valid locale string
	 * @return boolean true on success, else return false
	 */
	public function addLocale($language, $locale){
		if(sizeof($this->locales) > 0){
			$this->current_language = $language;
			$this->current_locale = $locale;
		}
		$this->locales[$language] = $locale;
	}
	
	public function addDateFormat($language, $id, $format='%D %T'){
		$this->date_formats[$language][$id] = $format;
	}
	
	public function getDateFormat($id){
		if(isset($this->date_formats[$this->getLanguage()][$id])){
			return new l18nDateConverter($this->date_formats[$this->getLanguage()][$id], $this->getTimezoneOffset());
		} else {
			return new l18nDateConverter($this->date_default_format, $this->getTimezoneOffset());
		}
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
	
	public function setLanguage($language){
		if(isset($this->locales[$language])){
			$this->current_locale = $this->locales[$language];
			$this->current_language = $language;
			setcookie(L18N_COOKIE_NAME, $language, time()+L18N_COOKIE_TIMEOUT, L18N_COOKIE_PATH);
			return true;
		} else {
			return false;
		}
	}
	
	public function setTimezone($timezone){
		$this->timezone = $timezone;
		setcookie(L18N_COOKIE_NAME.'_timezone', $timezone, time()+L18N_COOKIE_TIMEOUT, L18N_COOKIE_PATH);
		
		// Calculate time offset in seconds
		$default_timezone = timezone_open(L18N_DEFAULT_TIMEZONE);
		$timezone = timezone_open($timezone);
		$date = date_create(null, $default_timezone);
		$this->timezone_offset = $default_timezone->getOffset($date) - $timezone->getOffset($date);		
	}
	
	public function detectLanguage(){
		if(!isset($_COOKIE[L18N_COOKIE_NAME]) || !isset($languages[$_COOKIE[L18N_COOKIE_NAME]])){
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
					$this->setLanguage($language);
					break;
				} else if(strstr($language, '-')){
					list($suffix,) = explode('-', $language);
					if(isset($this->locales[$suffix])){
						$this->setLanguage($suffix);
						break;					
					}
				}
			}
		} else {
			$this->setLanguage($_COOKIE[L18N_COOKIE_NAME]);
		}
		return true;
	}
	
	public function getXML(DOMDocument $xml){
		$language = $xml->createElement('language');
		$language->setAttribute('language', $this->getLanguage());
		$language->setAttribute('locale', $this->getLocale());
		$language->setAttribute('timezone', $this->getTimezone());
		return $language;
	}
	public function &getArray(){
		
	}
}
	

class l18nDateConverter implements Converter {
	private $format = null;
	private $timezone_offset = 0;
	
	public function __construct($format, $timezone_offset=0){
		$this->format = $format;
		$this->timezone_offset = $timezone_offset;
	}	
	
	public function convert($data){
		return strftime($this->format, ($data - $this->timezone_offset));
	}
}

class l18nTimezones implements Output {
	private $timezones = null;
	
	public function __construct($timezones=null){
		 $this->timezones = $timezones;
	}
	
	public function getXML(DOMDocument $xml){
		if(is_null($this->timezones)){
			$this->timezones = timezone_identifiers_list();
		}
		$timezones = $xml->createElement('timezones');
		while(list($key, $val) = each($this->timezones)){
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
	
	public function &getArray(){
		
	}
}

?>