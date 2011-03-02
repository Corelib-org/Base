<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator PHP code block objects.
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
//*************** CodeGenrator PHP Code block class ***************//
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
class CodeGeneratorCodeBlockPHP extends CodeGeneratorCodeBlock {


	//*****************************************************************//
	//******** CodeGenrator PHP Code block class methods **************//
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
	public function __construct(&$source, $block, $prefix='/*', $suffix='*/'){
		parent::__construct($source, $block, $prefix, $suffix);
	}

	/**
	 * Has class constant
	 *
	 * check and see of the block has a class constant with
	 * the given name
	 *
	 * @param string $constant Constant name
	 * @return boolean true if constant exists, else return false
	 */
	public function hasClassConstant($constant){
		return preg_match('/const\s*'.preg_quote($constant, '/').'\s*=\s*[\'"]?(.*?)[\'"]?;/ms', $this->getSource());
	}

	/**
	 * Has class property
	 *
	 * check and see of the block has a class property with
	 * the given name
	 *
	 * @param string $property property name
	 * @return boolean true if property exists, else return false
	 */
	public function hasClassProperty($property){
		return preg_match('/(private|protected|public)\s*\$'.preg_quote($property, '/').'\s*=\s*[\'"]?(.*?)[\'"]?;/ms', $this->getSource());
	}

	/**
	 * Has class method
	 *
	 * check and see of the block has a class method with
	 * the given name
	 *
	 * @param string $method method name
	 * @return boolean true if method exists, else return false
	 */
	public function hasMethod($method){
		return preg_match('/(private|protected|public)\s*function\s*'.preg_quote($method, '/').'\s*\(.*?\)\s*\{/ms', $this->getSource());
	}

}


//*****************************************************************//
//************ CodeGenrator PHP Code statement class **************//
//*****************************************************************//
/**
 * CodeGenerator php code statement class.
 *
 * Class is used to make the code generator to help
 * other php generating classes do some basic tasks.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPStatement extends CodeGeneratorCodeBlockStatement {


	//*****************************************************************//
	//******** CodeGenrator PHP Code statement class methods **********//
	//*****************************************************************//
	/**
	 * Parse a PHP variable into source valid string.
	 *
	 * @return mixed
	 */
	public function parseValue($value){
		if(is_integer($value)){
			return 'null';
		} else if(is_null($value)){
			return 'null';
		} else if(is_bool($value)){
			if($value){
				return 'true';
			} else {
				return 'false';
			}
		} else {
			return '\''.addcslashes($value, '\'').'\'';
		}
	}
}


//*****************************************************************//
//******** CodeGenrator PHP Code statement class constant *********//
//*****************************************************************//
/**
 * CodeGenerator php code class constant statement.
 *
 * Class is used to make the code generator write a valid
 * class constant.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPClassConstant extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//**** CodeGenrator PHP Code statement class constant methods *****//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $constant constant name
	 * @param string $value constant value
	 * @return void
	 */
	public function __construct($constant, $value){
		parent::__construct('const '.$constant.' = '.$this->parseValue($value).';');
	}
}


//*****************************************************************//
//******** CodeGenrator PHP Code statement class property *********//
//*****************************************************************//
/**
 * CodeGenerator php code class constant statement.
 *
 * Class is used to make the code generator write a valid
 * class constant.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPClassProperty extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//**** CodeGenrator PHP Code statement class property methods *****//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $property constant name
	 * @param string $value constant value
	 * @return void
	 */
	public function __construct($visibility, $property, $value){
		parent::__construct($visibility.' $'.$property.' = '.$this->parseValue($value).';');
	}
}


//*****************************************************************//
//********* CodeGenrator PHP Code statement class method **********//
//*****************************************************************//
/**
 * CodeGenerator php code class method statement.
 *
 * Class is used to make the code generator write a valid
 * class method.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPClassMethod extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//*** CodeGenrator PHP Code statement class method properties *****//
	//*****************************************************************//
	/**
	 * @var string method name
	 */
	private $name = null;

	/**
	 * @var string visibility
	 */
	private $visibility = null;

	/**
	 * var CodeGeneratorCodeBlockPHPDoc docblock
	 */
	private $docblock = null;

	/**
	 * @var array method parameters
	 */
	private $parameters = array();

	//*****************************************************************//
	//***** CodeGenrator PHP Code statement class method methods ******//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $visibility method visibility
	 * @param string $name method name
	 * @param string $value constant value
	 * @return void
	 */
	public function __construct($visibility, $name){
		$this->visibility = $visibility;
		$this->name = $name;
	}

	/**
	 * Set docblock
	 *
	 * @param CodeGeneratorCodeBlockPHPDoc $docblock
	 * @return CodeGeneratorCodeBlockPHPDoc
	 */
	public function setDocBlock(CodeGeneratorCodeBlockPHPDoc $docblock){
		$this->docblock = $docblock;
		return $docblock;
	}

	/**
	 * Add parameter
	 *
	 * @param CodeGeneratorCodeBlockPHPParameter $parameter
	 * @return CodeGeneratorCodeBlockPHPParameter
	 */
	public function addParameter(CodeGeneratorCodeBlockPHPParameter $parameter){
		$this->parameters[] = $parameter;
		return $parameter;
	}

	/**
	 * Add new statement to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockPHPStatement $component
	 * @return CodeGeneratorCodeBlockPHPStatement
	 */
	public function addComponent(Composite $component, $reference=null){
		assert('$component instanceof CodeGeneratorCodeBlockPHPStatement');
		$this->components[] = $component;
		return $component;
	}

	/**
	 * Get composite.
	 *
	 * @return CodeGeneratorCodeBlockPHPClassMethod
	 */
	public function getComposite(){
		return $this;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$method  = '';
		if(!is_null($this->docblock)){
			$method .= $this->docblock->getSource($indent_offset);
		}

		if(count($this->parameters) > 0){
			foreach ($this->parameters as $parameter){
				$parameters[] = $parameter->getSource();
			}
			$parameters = implode(', ', $parameters);
		} else {
			$parameters = '';
		}

		$method .= $this->_getPrefix($indent_offset).$this->visibility.' function '.$this->name.'('.$parameters.'){'."\n";
		$method .= $this->_renderComponents(($indent_offset));
		$method .= $this->_getPrefix($indent_offset).'}'."\n";
		return $method;
	}
}


//*****************************************************************//
//******** CodeGenrator PHP Code statement parameter **************//
//*****************************************************************//
/**
 * CodeGenerator php code function/method parameter.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPParameter extends CodeGeneratorCodeBlockComposite {


	//*****************************************************************//
	//********* CodeGenrator PHP Code parameter properties ************//
	//*****************************************************************//
	/**
	 * @var string name
	 */
	private $name = null;

	/**
	 * @var string type
	 */
	private $type = null;

	/**
	 * @var string default
	 */
	private $default = null;


	//*****************************************************************//
	//*********** CodeGenrator PHP Code parameter methods *************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $name name
	 * @param string $default default value
	 * @return void
	 */
	public function __construct($name, $default=null){
		$this->name = $name;
		$this->default = $default;
	}

	/**
	 * Set parameter data type.
	 *
	 * @param string $type
	 * @return true on success, else return false
	 */
	public function setType($type){
		$this->type = $type;
		return true;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$parameter = '';
		if(!is_null($this->type)){
			$parameter .= $this->type.' ';
		}
		$parameter .= $this->name;
		if(!is_null($this->default)){
			$parameter .= '='.$this->default;
		}
		return $parameter;
	}
}


//*****************************************************************//
//*************** CodeGenrator PHP Code statement if **************//
//*****************************************************************//
/**
 * CodeGenerator php code class method statement.
 *
 * Class is used to make the code generator write a valid
 * class method.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPIf extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//********* CodeGenrator PHP Code statement if properties *********//
	//*****************************************************************//
	/**
	 * @var string condition
	 */
	private $condition = null;

	/**
	 * @var array alternation loops (elseif's)
	 */
	private $alternates = array();

	/**
	 * Else statement
	 *
	 * @var CodeGeneratorCodeBlockPHPIf
	 */
	private $else = null;

	/**
	 * @var CodeGeneratorCodeBlockPHPIf parent if statement
	 */
	private $parent = null;


	//*****************************************************************//
	//*********** CodeGenrator PHP Code statement if methods **********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $condition
	 * @return void
	 */
	public function __construct($condition){
		$this->condition = $condition;
	}

	/**
	 * Add new statement to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockPHPStatement $component
	 * @return CodeGeneratorCodeBlockPHPStatement
	 */
	public function addComponent(Composite $component, $reference=null){
		assert('$component instanceof CodeGeneratorCodeBlockPHPStatement');
		parent::addComponent($component, $reference);
		return $component;
	}

	/**
	 * Get composite.
	 *
	 * @return CodeGeneratorCodeBlockPHPIf
	 */
	public function getComposite(){
		return $this;
	}

	/**
	 * Add a alternate condition or else statement
	 *
	 * create new else or else if part in the fi loop, and return
	 * a new if loop instance. if $condition is null, the alternate
	 * condition is treated as a else statement.
	 *
	 * @param string $condition if condition
	 * @return CodeGeneratorCodeBlockPHPIf
	 */
	public function addAlternate($condition=null){
		$alternate = new CodeGeneratorCodeBlockPHPIf($condition);
 		$alternate->_setParent($this);
		if(is_null($condition)){
			$this->else = $alternate;
		} else {
			$this->alternates[$condition] = $alternate;
		}
		return $alternate;
	}

	/**
	 * Set alternate stement if parent.
	 *
	 * @param CodeGeneratorCodeBlockPHPIf $parent
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _setParent(Composite $parent){
		assert('$parent instanceof CodeGeneratorCodeBlockPHPIf');
		$this->parent = $parent;
		return true;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$if  = $this->_getPrefix($indent_offset);
		if(is_null($this->parent)){
			$if .= 'if('.$this->condition.'){'."\n";
		} else if(!is_null($this->condition)){
			$if .= '} else if('.$this->condition.'){'."\n";
		} else {
			$if .= '} else {'."\n";
		}

		$if .= $this->_renderComponents($indent_offset);

		foreach ($this->alternates as $alternate){
			$if .= $alternate->getSource( $indent_offset);
		}

		if(!is_null($this->else)){
		 	$if .= $this->else->getSource($indent_offset);
		}

		if(is_null($this->parent)){
			$if .= $this->_getPrefix($indent_offset).'}';
		}
		return $if;
	}
}


//*****************************************************************//
//************* CodeGenrator PHP Code statement while *************//
//*****************************************************************//
/**
 * CodeGenerator php code class method statement.
 *
 * Class is used to make the code generator write a valid
 * class method.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPWhile extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//******* CodeGenrator PHP Code statement while properties ********//
	//*****************************************************************//
	/**
	 * @var string condition
	 */
	private $condition = null;


	//*****************************************************************//
	//********* CodeGenrator PHP Code statement while methods *********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $condition
	 * @return void
	 */
	public function __construct($condition){
		$this->condition = $condition;
	}

	/**
	 * Add new statement to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockPHPStatement $component
	 * @return CodeGeneratorCodeBlockPHPStatement
	 */
	public function addComponent(Composite $component, $reference=null){
		assert('$component instanceof CodeGeneratorCodeBlockPHPStatement');
		parent::addComponent($component, $reference);
		return $component;
	}

	/**
	 * Get composite.
	 *
	 * @return CodeGeneratorCodeBlockPHPIf
	 */
	public function getComposite(){
		return $this;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$while  = $this->_getPrefix($indent_offset);
		$while .= 'while('.$this->condition.'){'."\n";
		$while .= $this->_renderComponents($indent_offset);
		$while .= $this->_getPrefix($indent_offset).'}';
		return $while;
	}
}


//*****************************************************************//
//***************** CodeGenrator PHP Code doc class ***************//
//*****************************************************************//
/**
 * CodeGenerator php code doc.
 *
 * Class is used to make the code generator write a phpdoc docblock.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPDoc extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//********** CodeGenrator PHP Code doc class properties ***********//
	//*****************************************************************//
	/**
	 * @var string docblock title
	 */
	private $title = null;

	/**
	 * @var string description
	 */
	private $description = null;

	/**
	 * @var array docblock tags
	 */
	private $tags = array();


	//*****************************************************************//
	//************ CodeGenrator PHP Code doc class methods ************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $title docblock title
	 * @return void
	 */
	public function __construct($title){
		$this->title = $title;
	}

	/**
	 * Set docblock description
	 *
	 * @param string $description
	 * @return boolean true on success, else return false
	 */
	public function setDescription($description){
		$this->description = $description;
		return false;
	}

	/**
	 * Add new doc tag to stack.
	 *
	 * @see Composite::addComponent()
	 * @param CodeGeneratorCodeBlockPHPDocTag $component
	 * @return CodeGeneratorCodeBlockPHPDocTag
	 */
	public function addComponent(Composite $component, $reference=null){
		assert('$component instanceof CodeGeneratorCodeBlockPHPDocTag');
		parent::addComponent($component, $reference);
		return $component;
	}

	/**
	 * Get composite.
	 *
	 * @return CodeGeneratorCodeBlockPHPDoc
	 */
	public function getComposite(){
		return $this;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$method  = $this->_getPrefix($indent_offset).'/**'."\n";
		$method .= $this->_getPrefix($indent_offset).' * '.$this->title.".\n";
		if(!is_null($this->description)){
			$method .= $this->_getPrefix($indent_offset).' * '.$this->description."\n";
		}
		if(count($this->components)){
			$method .= $this->_getPrefix($indent_offset).' *'."\n";
		}
		$method .= $this->_renderComponents(($indent_offset - 1));
		$method .= $this->_getPrefix($indent_offset).' */'."\n";
		return $method;
	}
}


//*****************************************************************//
//************* CodeGenrator PHP Code doc tag class ***************//
//*****************************************************************//
/**
 * CodeGenerator php code doc.
 *
 * Class is used to make the code generator write a phpdoc docblock.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockPHPDocTag extends CodeGeneratorCodeBlockPHPStatement {


	//*****************************************************************//
	//******** CodeGenrator PHP Code doc tag class properties *********//
	//*****************************************************************//
	/**
	 * @var string docblock tag
	 */
	private $tag = null;

	/**
	 * @var string description
	 */
	private $description = null;

	//*****************************************************************//
	//********* CodeGenrator PHP Code doc tag class methods ***********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $tag docblock tag
	 * @param string $description tag description
	 * @return void
	 */
	public function __construct($tag, $description=null){
		$this->tag = $tag;
		$this->description = $description;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		$method  = $this->_getPrefix($indent_offset).' * @'.$this->tag.' '.$this->description;
		return $method;
	}
}
?>