<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator XML code block objects.
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
 */

//*****************************************************************//
//*************** CodeGenrator XML Code block class ***************//
//*****************************************************************//
/**
 * CodeGenerator php code block.
 *
 * The code generator represents template block of PHP code.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockXML extends CodeGeneratorCodeBlock {


	//*****************************************************************//
	//******** CodeGenrator XSL Code block class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance of object.
	 *
	 * @see CodeGeneratorCodeBlock::__construct()
	 * @param string $source
	 * @param string $block block name
	 * @param string $prefix
	 * @param string $suffix
	 * @return void
	 */
	public function __construct(&$source, $block, $prefix='<!--', $suffix='-->'){
		parent::__construct($source, $block, $prefix, $suffix);
	}

	/**
	 * Add new statement to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockXMLStatement $component
	 * @return CodeGeneratorCodeBlockXMLStatement
	 */
	public function addComponent(CodeGeneratorCodeBlockXMLStatement $component){
		$this->components[] = $component;
		return $component;
	}
}


//*****************************************************************//
//************ CodeGenrator XML Code statement class **************//
//*****************************************************************//
/**
 * CodeGenerator XML code statement class.
 *
 * Class is used to make the code generator to help
 * other XML generating classes do some basic tasks.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockXMLStatement extends CodeGeneratorCodeBlockStatement {


	//*****************************************************************//
	//******** CodeGenrator PHP Code statement class methods **********//
	//*****************************************************************//

}


//*****************************************************************//
//************* CodeGenrator XML Code element class ***************//
//*****************************************************************//
/**
 * CodeGenerator XML code element class.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockXMLElement extends CodeGeneratorCodeBlockXMLStatement {


	//*****************************************************************//
	//******* CodeGenrator PHP Code element class properties **********//
	//*****************************************************************//
	/**
	 * @var array
	 */
	private $attributes = array();
	/**
	 * @var string
	 */
	private $name = null;


	//*****************************************************************//
	//********* CodeGenrator PHP Code element class methods ***********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $name element name
	 * @return void
	 */
	public function __construct($name){
		$this->name = $name;
	}

	/**
	 * Set element attribute.
	 *
	 * @param string $name attribute name
	 * @param string $value attribute value
	 * @return boolean true on success, else return false
	 */
	public function setAttribute($name, $value){
		$this->attributes[$name] = $value;
		return true;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$attributes = '';
		foreach($this->attributes as $name => $value){
			$attributes .= ' '.$name.'="'.$value.'"';
		}

		if(sizeof($this->components) > 0){
			$code = $this->_getPrefix($indent_offset).'<'.$this->name.$attributes.'>'."\n";
			$code .= $this->_renderComponents(($indent_offset));
			$code .= $this->_getPrefix($indent_offset).'</'.$this->name.'>';
			return $code;
		} else {
			return $this->_getPrefix($indent_offset).'<'.$this->name.$attributes.' />';
		}
	}

	/**
	 * Add new statement to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockXMLStatement $component
	 * @return CodeGeneratorCodeBlockXMLStatement
	 */
	public function addComponent(CodeGeneratorCodeBlockXMLStatement $component){
		$this->components[] = $component;
		return $component;
	}
}
?>