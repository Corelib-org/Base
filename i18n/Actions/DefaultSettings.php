<?php
namespace Corelib\Base\i18n\Actions;
use Corelib\Base\Event\Action, Corelib\Base\Event\Event;
use Corelib\Base\i18n\Localize, Corelib\Base\ServiceLocator\Locator;

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
class DefaultSettings extends Action {


	//*****************************************************************//
	//***** i18nApplyDefaultSettingsEventActions class methods ********//
	//*****************************************************************//
	/**
	 * Update method.
	 *
	 * @see EventAction::update()
	 */
	public function update(Event $event){
		$event->getPage()->addSettings(Locator::get('Corelib\Base\i18n\Localize'));
	}
}