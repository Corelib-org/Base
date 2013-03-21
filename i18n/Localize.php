<?php
namespace Corelib\Base\i18n;

use Corelib\Base\ServiceLocator\Service, Corelib\Base\ServiceLocator\Locator, Corelib\Base\Event\Handler;
use Corelib\Base\ServiceLocator\Autoloadable, Corelib\Base\Event\Event, Corelib\Base\Event\Action;
use Corelib\Base\Converters\Date\Strftime, Corelib\Base\PageFactory\Output, Corelib\Base\Core\Exception;
use DOMDocument;

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
class Localize implements Service,Autoloadable,Output {


	//*****************************************************************//
	//********************* i18n class properties *********************//
	//*****************************************************************//

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

	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get(__CLASS__);
	}

	/**
	 * i18n constructor.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct($cookie_name='i18n', $cookie_path='/', $cookie_timeout=31536000, $timezone=null, $base='share/lang/'){
		$this->cookie_name = $cookie_name;
		$this->cookie_path = $cookie_path;
		$this->cookie_timeout = $cookie_timeout;

		$this->base = $base;
		if(is_null($timezone)){
			$this->default_timezone = date('e');
		}
		$event = Locator::get('Corelib\Base\Event\Handler');
		$event->register(new Actions\DetectLanguage(), 'Corelib\Base\PageFactory\Events\RequestStart');
		$event->register(new Actions\DefaultSettings(), 'Corelib\Base\PageFactory\Events\ApplySettings');

		$this->getTimezone();
	}



	/**
	 * Add locale.
	 *
	 * @param i18nLocale $locale
	 * @return i18nLocale
	 */
	public function addLocale(Locale $locale){
		if(sizeof($this->locales) == 0){
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
		return $this->addLanguageFilePath($this->base.$this->locale->getLanguage().'/'.$filename);
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
			setcookie($this->cookie_name, $language, time()+$this->cookie_timeout, $this->cookie_path);
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
			return new Strftime($format);
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
		setcookie($this->cookie_name.'_timezone', $timezone, time()+$this->cookie_timeout, $this->cookie_path);

		Locator::get('Corelib\Base\Event\Handler')->trigger(new Events\TimezoneChange($this->timezone));

		// EventHandler::getInstance()->trigger(new i18nEventTimezoneChange($this->timezone));
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
				$this->setTimezone($this->default_timezone);
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

				if(is_null($this->locale)){
					if($this->fallback instanceof Locale){
						$this->setLocale($this->fallback->getLanguage());
					} else {
						throw new Exception('No fallback locale found, please add at least one locale before using i18n classes.', E_USER_ERROR);
					}
				}
			} else if($this->fallback instanceof i18nLocale){
				$this->setLocale($this->fallback->getLanguage());
			} else {
				throw new Exception('No fallback locale found, please add at least one locale before using i18n classes.', E_USER_ERROR);
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
?>