<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager cache status output class.
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
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Interfaces.php 5218 2010-03-16 13:07:41Z wayland $)
 * @internal
 */

//*****************************************************************//
//********************** ManagerConfig class **********************//
//*****************************************************************//
/**
 * Manager extension config class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @internal
 */
class ManagerConfig extends CorelibManagerExtension {


	//*****************************************************************//
	//**************** ManagerConfig class properties *****************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var ManagerConfig
	 * @internal
	 */
	private static $instance = null;


	//*****************************************************************//
	//****************** ManagerConfig class methods ******************//
	//*****************************************************************//

	/**
	 * ManagerConfig constructor.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		$event = EventHandler::getInstance();
		$event->register(new ManagerConfigAddSettings($this), 'Corelib\Base\PageFactory\Events\ApplySettings');
	}

	/**
	 * Return instance of ManagerConfig.
	 *
	 * Please refer to the {@link Singleton} interface for complete
	 * description.
	 *
	 * @see Singleton
	 * @uses ManagerConfig::$instance
	 * @return ManagerConfig
	 * @internal
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new ManagerConfig();
		}
		return self::$instance;
	}

	/**
	 * Get resource directory from extension settings.
	 *
	 * @param string $handle
	 * @return string directory on success, else return false.
	 * @internal
	 */
	public function getResourceDir($handle){
		if($resources = $this->getPropertyXML('resources')){
			$xpath = new DOMXPath($resources->ownerDocument);
			$xpath = $xpath->query('resource[@handle = \''.$handle.'\']', $resources);
			if($xpath->length > 0){
				return Manager::parseConstantTags($xpath->item(0)->nodeValue);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}


//*****************************************************************//
//********** ManagerConfigAddSettings class properties ************//
//*****************************************************************//
/**
 * Manager extension config add settings event action.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @internal
 */
class ManagerConfigAddSettings extends EventAction {


	//*****************************************************************//
	//*************** ManagerConfigAddSettings class ******************//
	//*****************************************************************//
	/**
	 * @var ManagerConfig
	 * @internal
	 */
	private $config = null;


	//*****************************************************************//
	//********** ManagerConfigAddSettings class methods ***************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param ManagerConfig $config
	 * @return void
	 * @internal
	 */
	public function __construct(ManagerConfig $config){
		$this->config = $config;
	}

	/**
	 * Update on event.
	 *
	 * @see EventAction::update()
	 * @internal
	 */
	public function update(Event $event){
		$event->getPage()->addSettings($this->config->getPropertyOutput('menu'));
	}
}
?>