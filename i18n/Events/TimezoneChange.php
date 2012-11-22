<?php

namespace Corelib\Base\i18n\Events;
use Corelib\Base\Event\Event;

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
class TimezoneChange implements Event {


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