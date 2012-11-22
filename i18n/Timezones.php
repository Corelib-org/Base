<?php

namespace Corelib\Base\i18n;
use Corelib\Base\PageFactory\Output, DOMDocument;

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
class Timezones implements Output {


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
?>