<?php
namespace Corelib\Base\i18n\Actions;
use Corelib\Base\Event\Action, Corelib\Base\Event\Event;

/**
 * i18nDetectLanguageEventActions class.
 *
 * @category corelib
 * @package Base
 * @subpackage i18n
 * @since 5.0
 */
class DetectLanguage extends Action {


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
?>