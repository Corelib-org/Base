<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator manager page.
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
 * @subpackage CodeGenerator
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Sørensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @since Version 5.0
 * @internal
 */

//*****************************************************************//
//****************** CodeGenerator Manager page *******************//
//*****************************************************************//
/**
 * Code generator HTTP GET request handler.
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 *
 * @since Version 5.0
 */
class WebPage extends ManagerPage {
	/**
	 * Build corelib manager page.
	 *
	 * @return void
	 */
	public function build(){
		$input = InputHandler::getInstance();

		$this->xsl->addTemplate('Base/Share/xsl/pages/generator.xsl');
		$this->addContent(ManagerConfig::getInstance()->getPropertyOutput('code-generator'));


		if($input->validateGet('object', new InputValidatorNotEmpty())){
			if($input->getGet('object') == 'ALL'){
				$generator = new CodeGenerator(ManagerConfig::getInstance()->getPropertyXML('code-generator'));
			} else {
				$generator = new CodeGenerator(ManagerConfig::getInstance()->getPropertyXML('code-generator'), $input->getGet('object'));
			}
			if($input->validateGet('write', new InputValidatorEquals('true'))){
				$generator->applyChanges();
			}
			$this->addContent($generator);
		}
	}
}
?>