<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator model plugin file class.
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
//******************* CodeGeneratorModel class ********************//
//*****************************************************************//
/**
 * CodeGenerator model file.
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 * @todo Add uses docblock tags to methods
 */
class CodeGeneratorModelFile extends CodeGeneratorFilePHP {


	//*****************************************************************//
	//***************** CodeGeneratorModel methods ********************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorModelFile.
	 *
	 * @param CodeGeneratorTable $table
	 * @param DOMElement $settings
	 * @param string $prefix
	 * @param string $group
	 * @see CodeGeneratorFile::__construct()
	 * @return void
	 * @internal
	 */
	public function __construct(CodeGeneratorTable $table, DOMElement $settings=null, $prefix=null, $group=null){
		parent::__construct($table, $settings, $prefix, $group);

		$prefix .= 'lib/class/';

		if(!is_null($group)){
			$prefix .= $group.'/';
		}

		$this->_setFilename($prefix.$this->getTable()->getClassName().'.php');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Model.php.generator');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeColumnConstants($this->content);
		$this->_writeProperties($this->content);
		$this->_writeEnumConstants($this->content);
		$this->_writeElement($this->content);
		$this->_writeGetters($this->content);
		$this->_writeSetters($this->content);
		$this->_writeConverters($this->content);
		$this->_writeArrayReader($this->content);
		$this->_writeXMLOutput($this->content);
		$this->_writeUtilityMethods($this->content);
		$this->_writeRelationChanges($this->content);
		$this->_writeCacheManager($this->content);
		$this->_writeRelationshipHelperInit($this->content);
		$this->_writeRelationshipHelperProperties($this->content);
		$this->_writeRelationshipHelperMethods($this->content);
		$this->_writeRelationshipHelperListProperties($this->content);
		$this->_writeRelationshipHelperListOptionProperties($this->content);
		$this->_writeRelationshipCommitActions($this->content);
		$this->_writeRelationshipOutput($this->content);
		$this->_writePrivateDataRetrievalMethods($this->content);
		$this->_writeAlternateConstructorMethods($this->content);
		$this->_writeORMCacheCleanup($this->content);
		$this->_writeCacheStoreCleanup($this->content);
	}

	/**
	 * Write column constants.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeColumnConstants(&$content){
		if($block = $this->_getCodeBlock($content, 'Field constants')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				$const = $column->getFieldConstantName();
				if(!$block->hasClassConstant($const)){
					$block->addComponent(new CodeGeneratorCodeBlockPHPClassConstant($const, $column->getName()));
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write column properties.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeProperties(&$content){
		if($block = $this->_getCodeBlock($content, 'Properties')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				$property = $column->getFieldVariableName();
				if(!$block->hasClassProperty($property)){
					$block->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('private', $property, null));
				}
				if($class = $column->getReferenceClassName() && !$block->hasClassProperty($property.'_id')){
					$block->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('private', $property.'_id', null));
				}

			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write column enum constants.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeEnumConstants(&$content){
		if($block = $this->_getCodeBlock($content, 'Enum constants')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->countValues() > 0){
					while(list(,$value) = $column->eachValues()){
						if(!$block->hasClassConstant($this->_makeEnumConstantName($column->getName(), $value))){
							$block->addComponent(new CodeGeneratorCodeBlockPHPClassConstant($this->_makeEnumConstantName($column->getName(), $value), $value));
						}
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create enum constant name
	 *
	 * @param string $field fieldname
	 * @param string $value value
	 * @return string enum constant
	 * @internal
	 */
	private function _makeEnumConstantName($field, $value){
		return strtoupper($field.'_'.$value);
	}

	/**
	 * Write column getters.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeGetters(&$content){
		$methods = array();
		if($block = $this->_getCodeBlock($content, 'Get methods')){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $this->getTable()->eachColumn()){
				$method_name = 'get'.$column->getFieldMethodName();

				$readable = $xpath->query('field[@name = "'.$column->getName().'" and @readable="false"]', $this->getSettings())->length;
				if(!$block->hasMethod($method_name) && $readable == 0){



					switch ($column->getType()){
						case CodeGeneratorColumn::TYPE_BOOLEAN:
							$type = 'boolean';
							break;
						case CodeGeneratorColumn::TYPE_FLOAT:
							$type = 'float';
							break;
						case CodeGeneratorColumn::TYPE_INTEGER:
							$type = 'integer';
							break;
						case CodeGeneratorColumn::TYPE_STRING:
							$type = 'string';
							break;
						default:
							$type = 'mixed';
					}

					$property_name = $column->getFieldVariableName();

					if($class = $column->getReferenceClassName()){
						$type = $class;

						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method_name));
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Get '.$column->getFieldVariableName());
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', $type.' '.$property_name));
						$method->setDocBlock($docblock);
						$method_name .= 'ID';

						$property_name .= '_id';
						$type = 'integer';

						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('is_null($this->'.$column->getFieldVariableName().')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = '.$class.'::getByID($this->'.$property_name.');'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$column->getFieldVariableName().';'));


					}
					$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method_name));
					$docblock = new CodeGeneratorCodeBlockPHPDoc('Get '.$column->getFieldVariableName().' ID');
					$method->setDocBlock($docblock);

					$converters = $xpath->query('field[@name = "'.$column->getName().'" and @converter="true"]', $this->getSettings())->length;
					$smarttypes = array(CodeGeneratorColumn::SMARTTYPE_SECONDS,
										CodeGeneratorColumn::SMARTTYPE_TIMESTAMP,
										CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);

					if($converters > 0 || in_array($column->getSmartType(), $smarttypes)){
						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('!is_null($this->'.$this->_makeConverterVariableName($column).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$this->_makeConverterVariableName($column).'->convert($this->'.$property_name.');'));

						$g = $if->addAlternate();
						$g->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property_name.';'));
						$type = 'mixed';
					} else {
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property_name.';'));
					}

					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', $type.' '.$property_name));

				}
			}
 			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Create converter variable name.
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string converter variable name
	 */
	protected function _makeConverterVariableName(CodeGeneratorColumn $column){
		return $column->getFieldVariableName().'_converter';
	}

	/**
	 * Write column setters.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeSetters(&$content){
		if($block = $this->_getCodeBlock($content, 'Set methods')){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);

			while(list(,$column) = $this->getTable()->eachColumn()){

				$writable = $xpath->query('field[@name = "'.$column->getName().'" and @writable="false"]', $this->getSettings())->length;

				$method = 'set'.$column->getFieldMethodName();
				if((!$block->hasMethod($method)) && $column->isWritable() && $column->getKey() != CodeGeneratorColumn::KEY_UNIQUE && $writable == 0){
					$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

					$reference_class = $xpath->query('field[@name = "'.$column->getName().'" and @reference-class=true()]', $this->getSettings());
					if($reference_class->length > 0){
						$class = $reference_class->item(0)->getAttribute('reference-class');
						$type = $class;
					} else if($class = $column->getReferenceClassName()){
						$type = $class;
					}

					$docblock = new CodeGeneratorCodeBlockPHPDoc('Set '.$column->getFieldVariableName());
					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($column, true).' $'.$column->getFieldVariableName()));
					$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
					$method->setDocBlock($docblock);
					if($column->isNull()){
						$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName(), 'null'));
					} else {
						$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
					}

					if($class){
						$param->setType($class);

						if($column->isNull()){
							$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('is_null($'.$column->getFieldVariableName().')'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = $'.$column->getFieldVariableName().';'));
							$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().'_id = $'.$column->getFieldVariableName().';'));
							$notnull = $if->addAlternate('!is_null($'.$column->getFieldVariableName().'->getID())');
						} else {
							$notnull = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('!is_null($'.$column->getFieldVariableName().'->getID())'));
							$if = $notnull;
						}

						$notnull->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = $'.$column->getFieldVariableName().';'));
						$notnull->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().'_id = $'.$column->getFieldVariableName().'->getID();'));
						$notnull->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->datahandler->set(self::'.$column->getFieldConstantName().', $'.$column->getFieldVariableName().', \'getID\');'));
						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('throw new BaseException(\''.$type.' is not a valid object: '.$type.'::getID() returned null\');'));

					} else {
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = $'.$column->getFieldVariableName().';'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->datahandler->set(self::'.$column->getFieldConstantName().', $'.$column->getFieldVariableName().');'));
					}
					$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
				}
			}

			while(list(,$index) = $this->getTable()->eachIndex()){
				$method = 'set'.$index->getIndexMethodName();
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE){
					if(!$block->hasMethod($method)){
						$parameters = array();
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Set '.$index->getName());
						$parameters = array();
						while(list(,$key) = $index->eachColumn()){
							$sethandler = array();
							$parameters = array();
							$sets = array();

							$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$key->getFieldVariableName()));
							if($class = $this->_getReferenceClass($key)){
								$param->setType($class);
								$parameters[] = '$'.$key->getFieldVariableName(); // ->getID() removed for compatability with isavailable methods
								$sethandler[] = new CodeGeneratorCodeBlockPHPStatement('$this->datahandler->set(self::'.$key->getFieldConstantName().', $'.$key->getFieldVariableName().'->getID());');
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' $'.$key->getFieldVariableName()));
							} else {
								$parameters[] = '$'.$key->getFieldVariableName();
								$sethandler[] = new CodeGeneratorCodeBlockPHPStatement('$this->datahandler->set(self::'.$key->getFieldConstantName().', $'.$key->getFieldVariableName().');');
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($key, true).' $'.$key->getFieldVariableName()));
							}
							$sets[] = new CodeGeneratorCodeBlockPHPStatement('$this->'.$key->getFieldVariableName().' = $'.$key->getFieldVariableName().';');
						}
						$method->setDocBlock($docblock);
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));

						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('$this->is'.$index->getIndexMethodName().'Available('.implode(', ', $parameters).')'));
						foreach ($sethandler as $set){
							$if->addComponent($set);
						}
						foreach ($sets as $set){
							$if->addComponent($set);
						}
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
					}
				}
			}

 			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write column converters.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 */
	protected function _writeConverters(&$content){
		if(($methods = $this->_getCodeBlock($content, 'Converter methods')) && ($properties = $this->_getCodeBlock($content, 'Converter properties'))){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $this->getTable()->eachColumn()){
				$method = 'set'.$column->getFieldMethodName().'Converter';
				$property = $column->getFieldVariableName().'_converter';
				$converters = $xpath->query('generator[@name = \'CodeGeneratorModel\']/field[@name = "'.$column->getName().'" and @converter="true"]', $this->getSettings()->parentNode)->length;
				$smarttypes = array(CodeGeneratorColumn::SMARTTYPE_SECONDS,
				                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP,
				                    CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE);

				if($converters > 0 || in_array($column->getSmartType(), $smarttypes)){
					if(!$methods->hasMethod($method)){
						$method = $methods->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

						$docblock = new CodeGeneratorCodeBlockPHPDoc('Set converter for '.$column->getFieldVariableName());
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', 'Converter $converter'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'Converter'));
						$method->setDocBlock($docblock);

						$parameter = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$converter'));
						$parameter->setType('Converter');
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property.' = $converter;'));
					}

					if(!$properties->hasClassProperty($property)){
						$properties->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('private', $property, null));
					}
				}
			}
			$this->_writeCodeBlock($content, $methods);
			$this->_writeCodeBlock($content, $properties);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write column array reader.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeArrayReader(&$content){

		if($block = $this->_getCodeBlock($content, 'setFromArray method content')){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $this->getTable()->eachColumn()){
				$contant = $column->getFieldConstantName();
				if(!$block->hasStatement('if ( isset( $array[self::'.$contant.'] ) ) {')){
					$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('isset($array[self::'.$contant.'])'));

					switch ($column->getType()){
						case CodeGeneratorColumn::TYPE_BOOLEAN:
							$type = 'bool';
							break;
						case CodeGeneratorColumn::TYPE_FLOAT:
							$type = 'float';
							break;
						case CodeGeneratorColumn::TYPE_INTEGER:
							$type = 'integer';
							break;
						case CodeGeneratorColumn::TYPE_STRING:
							$type = 'string';
							break;
					}

					$reference_class = $xpath->query('field[@name = "'.$column->getName().'" and @reference-class=true()]', $this->getSettings());
					if($reference_class->length > 0){
						$class = $reference_class->item(0)->getAttribute('reference-class');
					} else if($column->getReferenceClassName()){
						$class = $column->getReferenceClassName();
					} else {
						$class = false;
					}
					if(isset($type)){
						$type = '('.$type.') ';
					} else {
						$type = '';
					}
					if(!$class){
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = '.$type.'$array[self::'.$contant.'];'));
					} else {
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().'_id = '.$type.'$array[self::'.$contant.'];'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write XML output.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeXMLOutput(&$content){
		if($block = $this->_getCodeBlock($content, 'Get XML method')){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $this->getTable()->eachColumn()){

				$readable = $xpath->query('field[@name = "'.$column->getName().'" and @readable="false"]', $this->getSettings())->length;
				$property = $column->getFieldVariableName();
				$property_name = $property;
				$reference_class = $xpath->query('field[@name = "'.$column->getName().'" and @reference-class=true()]', $this->getSettings());
				if($reference_class->length > 0){
					$class = $reference_class->item(0)->getAttribute('reference-class');
				} else if($column->getReferenceClassName()){
					$class = $column->getReferenceClassName();
				} else {
					$class = false;
				}
				if($class){
					$property_name .= '_id';
				}

				if(!$block->hasStatementRegex('/if\s*\(\s*.*?\(\s*\$this->'.$property_name.'\s*\).*?\{/') && $readable == 0){
					$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('!is_null($this->'.$property_name.')'));
					if($column->getType() == CodeGeneratorColumn::TYPE_BOOLEAN){
						$bif = $if->addComponent(new CodeGeneratorCodeBlockPHPIf('!$this->'.$property));
						$bif->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$column->getTable()->getClassVariable().'->setAttribute(\''.$column->getFieldReadableVariableName().'\', \'false\');'));
						$belse = $bif->addAlternate();
						$belse->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$column->getTable()->getClassVariable().'->setAttribute(\''.$column->getFieldReadableVariableName().'\', \'true\');'));
					} else if($class){
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$column->getTable()->getClassVariable().'->setAttribute(\''.$column->getFieldReadableVariableName().'\', $this->'.$column->getFieldVariableName().'_id);'));
					} else {
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$column->getTable()->getClassVariable().'->setAttribute(\''.$column->getFieldReadableVariableName().'\', $this->get'.$column->getFieldMethodName().'());'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Write utility methods.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeUtilityMethods(&$content){
		if($block = $this->_getCodeBlock($content, 'Utility methods')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->getName() == 'sort_order'){
					$method = 'moveUp';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

						$docblock = new CodeGeneratorCodeBlockPHPDoc('Move object up in the manual sort order');
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
						$method->setDocBlock($docblock);

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_getDAO(false);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->dao->moveUp($this->id);'));
					}
					$method = 'moveDown';
					if(!$block->hasMethod($method)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $method));

						$docblock = new CodeGeneratorCodeBlockPHPDoc('Move object down in the manual sort order');
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
						$method->setDocBlock($docblock);

						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_getDAO(false);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->dao->moveDown($this->id);'));
					}
				}
			}
			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE){
					$methodname = 'is'.$index->getIndexMethodName().'Available';
					if(!$block->hasMethod($methodname)){
						$parameters = array();
						$methodname = 'is'.$index->getIndexMethodName().'Available';
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public', $methodname));
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Is '.$index->getName().' combination available');

						while(list(,$column) = $this->getTable()->getPrimaryKey()->eachColumn()){
							$parameters[] = '$this->'.$column->getFieldVariableName();
						}
						while(list(,$key) = $index->eachColumn()){

							$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$key->getFieldVariableName()));
							if($class = $this->_getReferenceClass($key)){
								$param->setType($class);
								$parameters[] = '$'.$key->getFieldVariableName().'->getID()';
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' $'.$key->getFieldVariableName()));
							} else {
								$parameters[] = '$'.$key->getFieldVariableName();
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $this->_getColumnDataType($key, true).' $'.$key->getFieldVariableName()));
							}
							$method->setDocBlock($docblock);
						}
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_getDAO(false);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->dao->'.$methodname.'('.implode(', ', $parameters).');'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
					}
				}
			}

			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get reference classname
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string reference class if found, else return false
	 */
	protected function _getReferenceClass(CodeGeneratorColumn $column){
		$xpath = new DOMXPath($this->getSettings()->ownerDocument);
		$reference_class = $xpath->query('enerator[@name = \'CodeGeneratorModel\']/field[@name = "'.$column->getName().'" and @reference-class=true()]', $this->getSettings()->parentNode);
		if($reference_class->length > 0){
			return $reference_class->item(0)->getAttribute('reference-class');
		} else if($class = $column->getReferenceClassName()){
			return $class;
		} else {
			return false;
		}
	}

	/**
	 * Write relation changes.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeRelationChanges(&$content){
		$primary = $this->getTable()->getPrimaryKey();

		if($primary && $primary->countColumns() > 1){
			$xpath = new DOMXPath($this->getSettings()->ownerDocument);
			while(list(,$column) = $primary->eachColumn()){
				$reference_class = $xpath->query('field[@name = "'.$column->getName().'" and @reference-class=true()]', $this->getSettings());
				$class = false;
				if($reference_class->length > 0){
					$class = $reference_class->item(0)->getAttribute('reference-class');
				} else if($column->getReferenceClassName()){
					$class = $column->getReferenceClassName();
				}

				$constructs[] = '$'.$column->getFieldVariableName().' = null';
				$dao[] = '$'.$column->getFieldVariableName();
				if($class){
					$constructs_body[] = "\t\t".'$this->'.$column->getFieldVariableName().' = new '.$class.'($'.$column->getFieldVariableName().');';
					$read[] = '$this->'.$column->getFieldVariableName().'->getID()';
				} else {
					$constructs_body[] = "\t\t".'$this->'.$column->getFieldVariableName().' = $'.$column->getFieldVariableName().';';
					$read[] = '$this->'.$column->getFieldVariableName();
				}
				$dao_update[] = '$this->'.$column->getFieldVariableName();
			}
			$content = preg_replace('/(function __construct\()(\$id = null)/', '\\1'.implode(', ', $constructs), $content);
			$content = preg_replace('/\s*\$this->id = \$id;/', "\n".implode("\n", $constructs_body), $content);
			$content = preg_replace('/(\$this->dao->read\()(\$this->id)(\))/', '\\1'.implode(', ', $read).'\\3', $content);
			$content = preg_replace('/(\$this->dao->update\()(\$this->id)/', '\\1'.implode(', ', $read), $content);
			$content = preg_replace('/(\$this->dao->delete\()(\$this->id)(\))/', '\\1'.implode(', ', $read).'\\3', $content);
			$content = preg_replace('/(\$this->dao->moveUp\()(\$this->id)(\))/', '\\1'.implode(', ', $read).'\\3', $content);
			$content = preg_replace('/(\$this->dao->moveDown\()(\$this->id)(\))/', '\\1'.implode(', ', $read).'\\3', $content);
			$content = preg_replace('/(function (update|delete|read)\()(\$id)/', '\\1'.implode(', ', $dao), $content);

			$content = preg_replace('/\s*\/\*\*\n(\s*\*.*?)*\n\s*\*\/(\n\s*public\s*function\s+create)/m','\\2', $content);
			$content = preg_replace('/public\s*function\s+create\s*\(.*?(\/\*\*|function)/ms','\\1', $content);

			$content = preg_replace('/if\(is_null\(\$this->id\)\)\{\n\s*\$r\s*=.*?$\n\s*\}.*?(\$r\s*=\s*.*?)$.*?\}/ms', '\\1', $content);

		}
		return true;
	}

	/**
	 * Write cache manager actions.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeCacheManager(&$content){
		if($block = $this->_getCodeBlock($content, 'Cache references')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if($column->isForeignKey() && $this->_getReferenceClass($column) && !$block->hasStatement('get'.$column->getFieldMethodName())){
					$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$cache->addObjectReferenceMethod(\'get'.$column->getFieldMethodName().'\');'));
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipHelperInit(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship helper init')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$property = $mapping->getReferenceKey()->getFieldVariableName().'_relations';
					if(!$block->hasStatementRegex('/\$this->'.$property.'\s+=/')){
						$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$property.' = new ORMRelationHelper(\''.$mapping->getTable()->getClassName().'\');'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipHelperProperties(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship helper properties')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$property = $mapping->getReferenceKey()->getFieldVariableName().'_relations';
					if(!$block->hasClassProperty($property)){
						$block->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('public', $property, null));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipHelperListOptionProperties(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship list option properties')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$property = $mapping->getReferenceKey()->getFieldVariableName().'_list_output';
					if(!$block->hasClassProperty($property)){
						$block->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('public', $property, false));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipHelperListProperties(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship list properties')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$property = $mapping->getReferenceKey()->getFieldVariableName().'_list';
					if(!$block->hasClassProperty($property)){
						$block->addComponent(new CodeGeneratorCodeBlockPHPClassProperty('public', $property, null));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipCommitActions(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship commit actions')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$class = $mapping->getReferenceKey()->getReferenceClassName(false);
					if(!$block->hasStatementRegex('/if\s*\(.*?\$this->_commit'.$class.'/')){
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$this->_commit'.$class.'()'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$r = true;'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipOutput(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship output')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$class = $mapping->getReferenceKey()->getReferenceClassName(false);
					$property = $mapping->getReferenceKey()->getFieldVariableName();
					if(!$block->hasStatementRegex('/if\s*\(.*?\$this->'.$property.'_list_output/')){
						$if = $block->addComponent(new CodeGeneratorCodeBlockPHPIf('$this->'.$property.'_list_output'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().'->appendChild($this->'.$property.'_list->getXML($xml));'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	public function _writeRelationshipHelperMethods(&$content){
		if($block = $this->_getCodeBlock($content, 'Relationship helper methods')){
			while(list(,$mapping) = $this->getTable()->eachTableMapping()){
				if($foreign_key = $mapping->getForeignKey()){
					$field = $mapping->getReferenceKey()->getFieldVariableName();
					$property = $field.'_relations';
					$class = $mapping->getReferenceKey()->getReferenceClassName();
					$method = 'add'.$class;

					if(!$block->hasMethod($method)){
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Add relationship to '.$class);
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' $'.$mapping->getReferenceKey()->getFieldVariableName().' Object to add a relationship to'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));

						$method = new CodeGeneratorCodeBlockPHPClassMethod('public', $method);
						$method->setDocBlock($docblock);
						$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($class.' $'.$mapping->getReferenceKey()->getFieldVariableName()));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property.'->add($'.$field.');'));
						$block->addComponent($method);
					}

					$method = 'remove'.$class;
					if(!$block->hasMethod($method)){
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Remove relationship from '.$class);
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' $'.$mapping->getReferenceKey()->getFieldVariableName().' Object to remove a relationship from'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));

						$method = new CodeGeneratorCodeBlockPHPClassMethod('public', $method);
						$method->setDocBlock($docblock);
						$method->addParameter(new CodeGeneratorCodeBlockPHPParameter($class.' $'.$mapping->getReferenceKey()->getFieldVariableName()));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property.'->remove($'.$field.');'));
						$block->addComponent($method);
					}

					$method = 'get'.$mapping->getReferenceKey()->getReferenceClassName(false);
					$property = $mapping->getReferenceKey()->getFieldVariableName();
					if(!$block->hasMethod($method)){
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Get list of relationships to '.$class);
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', '$output boolean if true, add list to output (eg. {@link '.$this->getTable()->getClassName().'::getXML()})'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', $mapping->getTable()->getClassName().'List'));

						$method = new CodeGeneratorCodeBlockPHPClassMethod('public', $method);
						$method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$output', 'false'));
						$method->setDocBlock($docblock);
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$property.'_list = new '.$mapping->getTable()->getClassName().'List();'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$property.'_list->set'.$this->getTable()->getClassName().'Filter($this);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$property.'_list_output = $output;'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $this->'.$property.'_list;'));
						$block->addComponent($method);
					}

					$method = '_commit'.$mapping->getReferenceKey()->getReferenceClassName(false);
					$property = $mapping->getReferenceKey()->getFieldVariableName();
					if(!$block->hasMethod($method)){
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Commit '.$class.' relationship changes');
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true if any changes was made, else return false'));

						$method = new CodeGeneratorCodeBlockPHPClassMethod('private', $method);
						$method->setDocBlock($docblock);
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$changed = false;'));
						$while = $method->addComponent(new CodeGeneratorCodeBlockPHPWhile('list($'.$property.', $relationship) = $this->'.$field.'_relations->each()'));
						$while->addComponent(new CodeGeneratorCodeBlockPHPStatement('$relationship->set'.$this->getTable()->getClassName().'($this);'));
						$while->addComponent(new CodeGeneratorCodeBlockPHPStatement('$relationship->set'.$class.'($'.$property.');'));
						$while->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$field.'_relations->commit($'.$property.', $relationship);'));
						$while->addComponent(new CodeGeneratorCodeBlockPHPStatement('$changed = true;'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $changed;'));
						$block->addComponent($method);
					}

				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}

	private function _writePrivateDataRetrievalMethods(&$content){
		if($block = $this->_getCodeBlock($content, 'Private data retrieval helper methods')){
 			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE || $index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
						$methodname = 'getByID';
						$indexname = 'ID';
					} else {
						$methodname = 'getBy'.$index->getIndexMethodName();
						$indexname = $index->getName();
					}
					if(!$block->hasMethod('_'.$methodname)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('private', '_'.$methodname));
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Get data by '.$indexname);
						$method->setDocBlock($docblock);
						$parameters = array();

						while(list(,$column) = $index->eachColumn()){
							switch ($column->getType()){
								case CodeGeneratorColumn::TYPE_BOOLEAN:
									$type = 'bool';
									break;
								case CodeGeneratorColumn::TYPE_FLOAT:
									$type = 'float';
									break;
								case CodeGeneratorColumn::TYPE_INTEGER:
									$type = 'integer';
									break;
								case CodeGeneratorColumn::TYPE_STRING:
									$type = 'string';
									break;
							}
							$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
							if($class = $this->_getReferenceClass($column)){
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' '.$column->getName()));
								$param->setType($class);
								$parameters[] = '$'.$column->getFieldVariableName().'->getID()';
							} else {
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $type.' '.$column->getName()));
								$parameters[] = '$'.$column->getFieldVariableName();
							}
						}
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_getDAO(false);'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->_setFromArray($this->dao->'.$methodname.'('.implode(', ', $parameters).'));'));
						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('is_null($this->id)'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return true;'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}

	}

	private function _writeAlternateConstructorMethods(&$content){
		if($block = $this->_getCodeBlock($content, 'Alternative constructors')){
 			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE || $index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
						$methodname = 'getByID';
						$indexname = 'ID';
					} else {
						$methodname = 'getBy'.$index->getIndexMethodName();
						$indexname = $index->getName();
					}
					if(!$block->hasMethod($methodname)){
						$method = $block->addComponent(new CodeGeneratorCodeBlockPHPClassMethod('public static', $methodname));
						$docblock = new CodeGeneratorCodeBlockPHPDoc('Create model instance based on '.$indexname);
						$method->setDocBlock($docblock);
						$parameters = array();

						while(list(,$column) = $index->eachColumn()){
							switch ($column->getType()){
								case CodeGeneratorColumn::TYPE_BOOLEAN:
									$type = 'bool';
									break;
								case CodeGeneratorColumn::TYPE_FLOAT:
									$type = 'float';
									break;
								case CodeGeneratorColumn::TYPE_INTEGER:
									$type = 'integer';
									break;
								case CodeGeneratorColumn::TYPE_STRING:
									$type = 'string';
									break;
							}
							$param = $method->addParameter(new CodeGeneratorCodeBlockPHPParameter('$'.$column->getFieldVariableName()));
							if($class = $this->_getReferenceClass($column)){
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $class.' '.$column->getName()));
								$param->setType($class);
								$parameters[] = '$'.$column->getFieldVariableName().'->getID()';
								$parameters_obj[] = '$'.$column->getFieldVariableName();
							} else {
								$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('param', $type.' '.$column->getName()));
								$parameters[] = '$'.$column->getFieldVariableName();
								$parameters_obj[] = '$'.$column->getFieldVariableName();
							}
						}
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('return', 'boolean true on success, else return false'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('uses', $this->getTable()->getClassName().'::_'.$methodname.'()'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('uses', 'ORMCache::getInstance()'));
						$docblock->addComponent(new CodeGeneratorCodeBlockPHPDocTag('uses', 'ORMCache::storeInstance()'));


						$if = $method->addComponent(new CodeGeneratorCodeBlockPHPIf('!$'.$this->getTable()->getClassVariable().' = ORMCache::getInstance(__CLASS__, __FUNCTION__, '.implode(', ', $parameters).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('$'.$this->getTable()->getClassVariable().' = new '.$this->getTable()->getClassName().'();'));

						$if = $if->addComponent(new CodeGeneratorCodeBlockPHPIf('$'.$this->getTable()->getClassVariable().'->_'.$methodname.'('.implode(', ', $parameters_obj).')'));
						$if->addComponent(new CodeGeneratorCodeBlockPHPStatement('ORMCache::storeInstance(__CLASS__, __FUNCTION__, $'.$this->getTable()->getClassVariable().'->_cacheStoreCleanup(), '.implode(', ', $parameters).');'));
						$else = $if->addAlternate();
						$else->addComponent(new CodeGeneratorCodeBlockPHPStatement('return false;'));
						$method->addComponent(new CodeGeneratorCodeBlockPHPStatement('return $'.$this->getTable()->getClassVariable().';'));
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}

	}

	private function _writeORMCacheCleanup(&$content){
		if($block = $this->_getCodeBlock($content, 'ORMCache cleanup after commit')){
 			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE || $index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
						$methodname = 'getByID';
					} else {
						$methodname = 'getBy'.$index->getIndexMethodName();
					}
					if(!$block->hasStatementRegex('/ORMCache::removeInstance\(__CLASS__, \''.$methodname.'\'/')){
						$parameters = array();
						while(list(,$column) = $index->eachColumn()){
							if($class = $this->_getReferenceClass($column)){
								$parameters[] = '$this->'.$column->getFieldVariableName().'_id';
							} else {
								$parameters[] = '$this->'.$column->getFieldVariableName();
							}
						}

						$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('ORMCache::removeInstance(__CLASS__, \''.$methodname.'\', '.implode(', ', $parameters).');'));
					}

				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}


	private function _writeCacheStoreCleanup(&$content){
		if($block = $this->_getCodeBlock($content, '_cacheStore method content')){
 			while(list(,$index) = $this->getTable()->eachIndex()){
				if($index->getType() == CodeGeneratorColumn::KEY_UNIQUE || $index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
					if($index->getType() == CodeGeneratorColumn::KEY_PRIMARY){
						$methodname = 'getByID';
					} else {
						$methodname = 'getBy'.$index->getIndexMethodName();
					}
					while(list(,$column) = $index->eachColumn()){
						if(!$block->hasStatementRegex('/\$this->'.$column->getFieldVariableName().'\s+=/') && $this->_getReferenceClass($column)){
							$block->addComponent(new CodeGeneratorCodeBlockPHPStatement('$this->'.$column->getFieldVariableName().' = null;'));
						}
					}
				}
			}
			$this->_writeCodeBlock($content, $block);
			return true;
		} else {
			return false;
		}
	}


/*

 	private function _commitServices(){
		$changed = false;
		while(list($relation, $link) = $this->service_relations->each()){
			$link->setServer($this);
			$link->setService($relation);
			$this->service_relations->commit($relation, $link);
			$changed = true;
		}
		return $changed;
	}
*/

	/**
	 * Write element name.
	 *
	 * @param string $content
	 * @return boolean true on success, else return false
	 * @internal
	 */
	private function _writeElement(&$content){
		$content = str_replace('${element}', $this->getTable()->getTableReadableVariableName(), $content);
	}
}
?>