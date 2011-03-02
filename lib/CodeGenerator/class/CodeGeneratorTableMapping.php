<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator table mappings.
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
//************ CodeGenerator Output and control class *************//
//*****************************************************************//
/**
 * CodeGenerator table mapping class.
 *
 * This class is able to gather information about a table mapping.
 *
 * @category corelib
 * @package Base
 * @subpackage CodeGenerator
 * @since Version 5.0
 * @author Steffen Sørensen <ss@corelib.org>
 */
class CodeGeneratorTableMapping {
	private $table = null;
	private $foreign_key = false;
	private $reference_key = null;


	public function __construct(CodeGeneratorTable $table, $reference_key, $foreign_key=false){
		$this->table = $table;
		while(list(,$column) = $table->eachColumn()){
			if($column->getName() == $reference_key){
				$this->reference_key = $column;
			}
			if(!is_null($foreign_key)){
				if($column->getName() == $foreign_key){
					$this->foreign_key = $column;
				}
			}
		}
	}

	public function getForeignKey(){
		return $this->foreign_key;
	}

	public function getReferenceKey(){
		return $this->reference_key;
	}

	public function getTable(){
		return $this->table;
	}
}
?>