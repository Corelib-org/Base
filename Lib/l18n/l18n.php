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

class l18n implements Singleton {
	/**
	 * @var l18n
	 */
	private static $instance = null;
	
	private $locales = array();
	
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
	
	public function getLanguage(){
		return $this->current_language;
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
}

?>