<?php
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
		$this->addLanguageFilePath(I18N_LANGUAGE_BASE.$this->getLanguage().'/'.$filename);
	}
	public function addLangaugeFilePath($filename){
		$this->language_files[] = $filename;
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
		$timezone = timezone_open($timezone);
		$date = date_create(null, $default_timezone);
		//echo date('r', $default_timezone->getOffset($date))."\n";
		//echo date('r', $timezone->getOffset($date)),"\n";
		$this->timezone_offset = $default_timezone->getOffset($date) - $timezone->getOffset($date);		
	}
	
	public function detectLanguage(){
		if(!isset($_COOKIE[$this->cookie_name]) || !isset($this->languages[$_COOKIE[$this->cookie_name]])){
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
	public function &getArray(){
		
	}
}
	


?>