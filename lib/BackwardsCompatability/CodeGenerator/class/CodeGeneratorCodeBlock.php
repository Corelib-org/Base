<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator code block objects
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

use Corelib\Base\Core\Composite as Composite;
//*****************************************************************//
//********* CodeGenrator Code block composite class ***************//
//*****************************************************************//
/**
 * CodeGenerator code block composite.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
abstract class CodeGeneratorCodeBlockComposite extends \Corelib\Base\Core\Composite {


	//*****************************************************************//
	//****** CodeGenrator Code block composite class constants ********//
	//*****************************************************************//
	/**
	 * Indention charecter.
	 *
	 * @var string
	 */
	const PREFIX_CHARACTER = "\t";


	//*****************************************************************//
	//***** CodeGenrator Code block composite class properties ********//
	//*****************************************************************//
	/**
	 * Code block statements.
	 *
	 * @var array list of statements to write into the code block
	 */
	protected $components = array();


	//*****************************************************************//
	//*** CodeGenrator Code block composite class abstract methods ****//
	//*****************************************************************//
	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	abstract public function getSource($indent_offset=0);


	//*****************************************************************//
	//******** CodeGenrator Code block composite class methods ********//
	//*****************************************************************//
	/**
	 * Has statement
	 *
	 * @param string $statement
	 * @return boolean true if statement exists, else return false
	 */
	public function hasStatement($statement){
		return $this->hasStatementRegex('/'.preg_replace('/\s/', '\s*', preg_quote($statement, '/')).'/ms');
	}

	/**
	 * Has statement regex
	 *
	 * @param string $regex
	 * @return boolean true if statement exists, else return false
	 */
	public function hasStatementRegex($regex){
		return preg_match($regex, $this->getSource());
	}


	//*****************************************************************//
	//****** CodeGenrator Code block composite class constants ********//
	//*****************************************************************//
	/**
	 * Render source code components.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code from components
	 */
	protected function _renderComponents($indent_offset){
		$indent_offset++;
		$source = '';
		foreach ($this->components as $component) {
			$source .= $component->getSource($indent_offset)."\n";
		}
		return $source;
	}

	/**
	 * Create indentation prefix.
	 *
	 * return indentation prefix based on $indent_offset
	 *
	 * @param integer $indent_offset
	 * @return string indentation prefix
	 */
	protected function _getPrefix($indent_offset){
		return str_repeat(CodeGeneratorCodeBlockComposite::PREFIX_CHARACTER, $indent_offset);
	}
}


//*****************************************************************//
//************** CodeGenrator Code block class ********************//
//*****************************************************************//
/**
 * CodeGenerator code block.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlock extends CodeGeneratorCodeBlockComposite {


	//*****************************************************************//
	//*********** CodeGenerator Code block class constants ************//
	//*****************************************************************//
	/**
	 * Code block end token.
	 *
	 * @var string
	 * @internal
	 */
	const BLOCK_END_TOKEN = 'end';


	//*****************************************************************//
	//********** CodeGenerator Code block class propertues ************//
	//*****************************************************************//
	/**
	 * Code block indentation offset.
	 *
	 * @var integer indent offset
	 * @internal
	 */
	private $indent_offset = 0;

	/**
	 * Code block lines.
	 *
	 * @var array block lines
	 * @internal
	 */
	private $block = array();

	/**
	 * @var string block name
	 * @internal
	 */
	private $name = null;

	/**
	 * @var string prefix
	 * @internal
	 */

	private $prefix = null;

	/**
	 * @var string suffix
	 * @internal
	 */
	private $suffix = null;


	//*****************************************************************//
	//*********** CodeGenerator Code block class methods **************//
	//*****************************************************************//
	/**
	 * Create new instance of object.
	 *
	 * @see CodeGeneratorFile::_getCodeBlock()
	 * @uses CodeGeneratorCodeBlock::$indent_offset
	 * @uses CodeGeneratorCodeBlock::$block
	 * @throws BaseException if code block has a ending tag mismatch
	 * @param string $source
	 * @param string $block block name
	 * @param string $prefix
	 * @param string $suffix
	 * @return void
	 */
	public function __construct(&$source, $block, $prefix, $suffix){
		assert('is_string($source)');
		assert('is_string($block)');
		assert('is_string($prefix)');
		assert('is_string($suffix)');

		$this->name = $block;
		$this->prefix = $prefix;
		$this->suffix = $suffix;

		$lines = explode("\n", $source);
		$inblock = false;
		foreach ($lines as $no => $line){
			if(strstr($line, $this->getStartToken())){
				$this->indent_offset = strlen(preg_replace('/(\t*).*?$/', '\\1', $line)) - 1;
				$inblock = true;
			}

			if($inblock){
				$this->block[] = $line;
			}

			if(strstr($line, $this->getEndToken())){
				$inblock = false;
				break;
			}
		}

		if($inblock){
			$this->block = array();
			throw new BaseException('Code block not closed correctly, ending tag "'.$this->getEndToken().'" is missing in template: '.$block, E_USER_ERROR);
		}
	}

	/**
	 * Get code block start token.
	 *
	 * @return string start token
	 */
	public function getStartToken(){
		return $this->prefix.' '.$this->name.' '.$this->suffix;
	}

	/**
	 * Get code block end token.
	 *
	 * @return string end
	 */
	public function getEndToken(){
		return $this->prefix.' '.$this->name.' '.CodeGeneratorCodeBlock::BLOCK_END_TOKEN.' '.$this->suffix;
	}

	/**
	 * Get code block name.
	 *
	 * @return string name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Get code block prefix.
	 *
	 * @return string prefix
	 */
	public function getPrefix(){
		return $this->prefix;
	}

	/**
	 * Get code block suffix.
	 *
	 * @return string suffix
	 */
	public function getSuffix(){
		return $this->suffix;
	}

	/**
	 * Check if code block exists.
	 *
	 * @return boolean true on success, else return false
	 */
	public function exist(){
		if(sizeof($this->block) > 0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add Code block statement to code block.
	 *
	 * @param CodeGeneratorCodeBlockStatement $component
	 * @return return CodeGeneratorCodeBlockStatement
	 */
	public function addComponent(Composite $component, $reference=null){
		assert('$component instanceof CodeGeneratorCodeBlockStatement');
		parent::addComponent($component, $reference);
		return $component;
	}

	/**
	 * Get composite.
	 *
	 * @return CodeGeneratorCodeBlock
	 */
	public function getComposite(){
		return $this;
	}

	/**
	 * Get code block source.
	 *
	 * @param integer $indent_offset indentation offset
	 * @return string updated code block
	 * @internal
	 */
	public function getSource($indent_offset=0){
		$block = $this->block;
		$end = array_pop($block);

		$source = implode("\n", $block)."\n";
		$source .= $this->_renderComponents($this->indent_offset);
		$source .= $end;
		return $source;
	}

	/**
	 * Get line count for block.
	 *
	 * @return integer line count.
	 */
	public function getLineCount(){
		return count($this->block);
	}
}


//*****************************************************************//
//*********** CodeGenrator Code block statement class *************//
//*****************************************************************//
/**
 * CodeGenerator code block statement.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorCodeBlockStatement extends CodeGeneratorCodeBlockComposite {


	//*****************************************************************//
	//****** CodeGenrator Code block statement class properties *******//
	//*****************************************************************//
	/**
	 * Code statement.
	 *
	 * @var string
	 */
	private $statement = null;


	//*****************************************************************//
	//******* CodeGenrator Code block statement class methods *********//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $statement code statement
	 * @return void
	 */
	public function __construct($statement){
		assert('is_string($statement)');
		$this->statement = $statement;
	}

	/**
	 * Get source code.
	 *
	 * @param integer $indent_offset
	 * @return string generated source code.
	 */
	public function getSource($indent_offset=0){
		return $this->_getPrefix($indent_offset).$this->statement;
	}
}
?>