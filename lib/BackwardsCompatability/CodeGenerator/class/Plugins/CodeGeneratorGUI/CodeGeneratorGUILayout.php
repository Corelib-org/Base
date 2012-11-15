<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Code generator gui plugin definition class.
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
//**************** CodeGeneratorGUILayout class *******************//
//*****************************************************************//
/**
 * CodeGenerator gui view
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @package Base
 * @subpackage CodeGenerator
 * @category corelib
 * @since Version 5.0
 */
class CodeGeneratorGUILayout extends CodeGeneratorGUIFileXSL {


	//*****************************************************************//
	//*************** CodeGeneratorGUILayout methods ******************//
	//*****************************************************************//
	/**
	 * create new instance of CodeGeneratorGUILayout.
	 *
	 * @uses CodeGeneratorTable::getTableReadableVariableName()
	 * @uses CodeGeneratorFile::getTable()
	 * @uses CodeGeneratorFile::_setFilename()
	 * @uses CodeGeneratorFile::_loadContent()
	 * @uses CodeGeneratorFile::$settings
	 * @uses CodeGeneratorFileXML::_getReadableGroup()
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

		$prefix .= 'share/xsl/base/';

		if(!is_null($group)){
			$prefix .= $this->_getReadableGroup($group).'/';
		}

		if($this->settings->getAttribute('action') == 'default'){
			$filename = 'view';
		} else {
			$filename = 'view-'.$this->settings->getAttribute('action');
		}

		$this->_setFilename($prefix.$this->getTable()->getTableReadableVariableName().'.xsl');
		$this->_loadContent(CORELIB.'/Base/Share/Generator/Layout.xsl.generator');
	}

	/**
	 * Generate code.
	 *
	 * @see CodeGeneratorFile::generate()
	 * @return void
	 */
	public function generate(){
		$this->_writeXSLLayoutMode($this->content, $this->settings->getAttribute('xsl-layout-mode'));
		$this->_writeXSLLayoutTemplate($this->content, $this->settings->getAttribute('layout'));
		$this->_writeXSLTemplate($this->content, 'base/'.$this->_getReadableGroup($this->getGroup()).'/'.$this->getTable()->getTableReadableVariableName().'.xsl');

		if($block = $this->_getCodeBlock($this->content, 'Lists')){
			$list = $this->settings->getElementsByTagName('list');
			for($i = 0; $i < $list->length; $i++){
				if(strlen(trim($list->item($i)->getAttribute('render-mode'))) <= 0){
					$list->item($i)->setAttribute('render-mode', 'table');
				}
				if(strlen(trim($list->item($i)->getAttribute('xsl-mode'))) <= 0){
					if($list->item($i)->getAttribute('render-mode') == 'select-options'){
						$list->item($i)->setAttribute('xsl-mode', 'form-select-options');
					} else {
						$list->item($i)->setAttribute('xsl-mode', 'xhtml-list');
					}
				}
				if((!$block->hasStatement('match="'.$this->getTable()->getTableReadableVariableName().'-list/'.$this->getTable()->getTableReadableVariableName().'"')) || (!$block->hasStatement('mode="'.$list->item($i)->getAttribute('xsl-mode').'"'))){
					$this->writeListTemplates($list->item($i), $block);
					$block->addComponent(new CodeGeneratorCodeBlockXMLStatement(''));
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}

		if($block = $this->_getCodeBlock($this->content, 'Edit')){
			while(list(,$column) = $this->getTable()->eachColumn()){
				if(!$this->hasFieldEdit($block, $column) && $column->isWritable()){
					$block->addComponent($this->writeFieldEditTemplate($block, $column));
					$block->addComponent(new CodeGeneratorCodeBlockXMLStatement(''));
				}
			}
			$this->_writeCodeBlock($this->content, $block);
		}

		if($block = $this->_getCodeBlock($this->content, 'View')){
			if(!$this->hasFieldView($block)){
				$block->addComponent($this->writeFieldViewTemplate($block));
			}
			$this->_writeCodeBlock($this->content, $block);
		}
	}

	/**
	 * Write list templates.
	 *
	 * @todo should be splittet en to small methods.
	 * @param DOMElement $list
	 * @param CodeGeneratorCodeBlockXML $block
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	final public function writeListTemplates(DOMElement $list, CodeGeneratorCodeBlockXML $block){
		$outer = new CodeGeneratorCodeBlockXMLElement('xsl:template');
		$outer->setAttribute('match', $this->getTable()->getTableReadableVariableName().'-list');
		$outer->setAttribute('mode', $list->getAttribute('xsl-mode'));
		$block->addComponent($outer);

		$inner = $block->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:template'));
		$inner->setAttribute('match', $this->getTable()->getTableReadableVariableName().'-list/'.$this->getTable()->getTableReadableVariableName());
		$inner->setAttribute('mode', $list->getAttribute('xsl-mode'));

		$fields = array();
		$DOMfields = $list->getElementsByTagName('field');
		for($i = 0; $i < $DOMfields->length; $i++){
			$fields[] = $DOMfields->item($i)->getAttribute('name');
		}

		if(sizeof($fields) == 0){
			$fields = false;
		}
		switch ($list->getAttribute('render-mode')){
			case 'ul':
				$ul = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('ul'));
				$ul->setAttribute('class', 'list');

				$apply = $ul->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', $this->getTable()->getTableReadableVariableName());
				$apply->setAttribute('mode', $list->getAttribute('xsl-mode'));

				while(list(,$column) = $this->getTable()->eachColumn()){
					if(($fields === false || in_array($column->getName(), $fields)) && !$ul->hasStatementRegex('/\<span.*?class=\".*?'.$column->getFieldReadableVariableName().'/')){
						$li = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('li'));
						$value = $li->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
						$value->setAttribute('select', '@'.$column->getFieldReadableVariableName());
					}
				}
				return $ul;
				break;
			case 'ol':
				$ol = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('ol'));
				$ol->setAttribute('class', 'list');

				$apply = $ol->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', $this->getTable()->getTableReadableVariableName());
				$apply->setAttribute('mode', $list->getAttribute('xsl-mode'));

				while(list(,$column) = $this->getTable()->eachColumn()){
					if(($fields === false || in_array($column->getName(), $fields)) && !$ol->hasStatementRegex('/\<span.*?class=\".*?'.$column->getFieldReadableVariableName().'/')){
						$li = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('li'));
						$value = $li->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
						$value->setAttribute('select', '@'.$column->getFieldReadableVariableName());
					}
				}
				return $ol;
				break;
			case 'simple':
				$div = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('div'));
				$div->setAttribute('class', 'list');
				$apply = $div->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', $this->getTable()->getTableReadableVariableName());
				$apply->setAttribute('mode', $list->getAttribute('xsl-mode'));

				while(list(,$column) = $this->getTable()->eachColumn()){
					if(($fields === false || in_array($column->getName(), $fields)) && !$div->hasStatementRegex('/\<span.*?class=\".*?'.$column->getFieldReadableVariableName().'/')){
						$value = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
						$value->setAttribute('select', '@'.$column->getFieldReadableVariableName());
					}
				}
				return $div;
				break;
			case 'select-options':
				$param = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:param'));
				$param->setAttribute('name', 'select');

				$apply = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', $this->getTable()->getTableReadableVariableName());
				$apply->setAttribute('mode', $list->getAttribute('xsl-mode'));

				$param = $apply->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:with-param'));
				$param->setAttribute('name', 'select');
				$param->setAttribute('select', '$select');

				$param = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:param'));
				$param->setAttribute('name', 'select');

				$option = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:element'));
				$option->setAttribute('name', 'option');
				$attribute = $option->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:attribute'));
				$attribute->setAttribute('name', 'value');
				$value = $attribute->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
				$value->setAttribute('select', '@id');

				$if = $option->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:if'));
				$if->setAttribute('test', '@id = $select');
				$attribute = $if->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:attribute'));
				$attribute->setAttribute('name', 'selected');
				$attribute->addComponent(new CodeGeneratorCodeBlockXMLStatement('true'));

				$if = $option->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
				$if->setAttribute('select', '@id');
				break;
			default:
				$columns = 0;

				$table = $outer->addComponent(new CodeGeneratorCodeBlockXMLElement('table'));
				$table->setAttribute('class', 'list');
				$table->setAttribute('id', $this->getTable()->getTableReadableVariableName().'-list');
				$thead = $table->addComponent(new CodeGeneratorCodeBlockXMLElement('thead'));
				$tfoot = $table->addComponent(new CodeGeneratorCodeBlockXMLElement('tfoot'));
				$tbody = $table->addComponent(new CodeGeneratorCodeBlockXMLElement('tbody'));
				$apply = $tbody->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', $this->getTable()->getTableReadableVariableName());
				$apply->setAttribute('mode', $list->getAttribute('xsl-mode'));
				$tr = $inner->addComponent(new CodeGeneratorCodeBlockXMLElement('tr'));

				while(list(,$column) = $this->getTable()->eachColumn()){
					if(($fields === false || in_array($column->getName(), $fields)) && !$table->hasStatementRegex('/\<th.*?class=\".*?'.$column->getFieldReadableVariableName().'/')){
						$th = $thead->addComponent(new CodeGeneratorCodeBlockXMLElement('th'));
						$th->setAttribute('class', $this->getFieldCSSClass($column).' '.$column->getFieldReadableVariableName());
						$th->addComponent(new CodeGeneratorCodeBlockXMLStatement($column->getFieldReadableVariableName()));
						$columns++;
					}
					if(($fields === false || in_array($column->getName(), $fields)) && !$table->hasStatementRegex('/\<td.*?class=\".*?'.$column->getFieldReadableVariableName().'/')){
						$td = $tr->addComponent(new CodeGeneratorCodeBlockXMLElement('td'));
						$td->setAttribute('class', $this->getFieldCSSClass($column).' '.$column->getFieldReadableVariableName());
						$value = $td->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
						$value->setAttribute('select', '@'.$column->getFieldReadableVariableName());
					}
				}

				if($list->getAttribute('draw-actions') == 'true'){
					$columns++;
					$th = $thead->addComponent(new CodeGeneratorCodeBlockXMLElement('th'));
					$th->setAttribute('class', 'actions');
					$th->addComponent(new CodeGeneratorCodeBlockXMLStatement('actions'));

					$td = $tr->addComponent(new CodeGeneratorCodeBlockXMLElement('td'));
					$td->setAttribute('class', 'actions');

					$xpath = new DOMXPath($this->settings->ownerDocument);
					$list = $xpath->query('view|edit|delete', $this->settings->parentNode);
					if($list->length > 0){
						for($i = 0; $i < $list->length; $i++){
							$td->addComponent($this->writeListActionLink($list->item($i)));
						}
					}
				}

				$pager_colspan = 2 % $columns;
				$count_colspan = $columns - $pager_colspan;

				$tr = $tfoot->addComponent(new CodeGeneratorCodeBlockXMLElement('tr'));
				$td = $tr->addComponent(new CodeGeneratorCodeBlockXMLElement('td'));
				$td->setAttribute('class', 'pager');
				$td->setAttribute('colspan', $pager_colspan);
				$apply = $td->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:apply-templates'));
				$apply->setAttribute('select', 'pager');

				$td = $tr->addComponent(new CodeGeneratorCodeBlockXMLElement('td'));
				$td->setAttribute('class', 'count');
				$td->setAttribute('colspan', $count_colspan);
				$apply = $td->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
				$apply->setAttribute('select', 'concat(@count, \' '.$this->getTable()->getTableReadableVariableName().'\')');
				break;
		}

	}

	/**
	 * Write list action link.
	 *
	 * write list action link and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @param DOMElement $action
	 * @see CodeGeneratorGUILayout::writeListActionLinkContent()
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeListActionLink(DOMElement $action){
		$a = new CodeGeneratorCodeBlockXMLElement('a');
		$a->setAttribute('href', str_replace('${id}', '{@id}', $action->getAttribute('gui-url')));
		$a->addComponent($this->writeListActionLinkContent($action));
		return $a;
	}

	/**
	 * Write list action link content.
	 *
	 * write list action link content and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @param DOMElement $action
	 * @see CodeGeneratorGUILayout::writeListActionLink()
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeListActionLinkContent(DOMElement $action){
		return new CodeGeneratorCodeBlockXMLStatement($action->getAttribute('method'));
	}

	/**
	 * Get Field CSS class.
	 *
	 * get field css class according to its datatype
	 *
	 * @param CodeGeneratorColumn $column
	 * @return string css class name
	 */
	public function getFieldCSSClass(CodeGeneratorColumn $column){
		switch ($column->getSmartType()){
			case CodeGeneratorColumn::TYPE_BOOLEAN:
				$class = 'boolean';
				break;
			case CodeGeneratorColumn::TYPE_FLOAT:
				$class = 'number';
				break;
			case CodeGeneratorColumn::TYPE_INTEGER:
				$class = 'number';
				break;
			case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP:
				$class = 'date';
				break;
			case CodeGeneratorColumn::SMARTTYPE_TIMESTAMP_SET_ON_CREATE:
				$class = 'date';
				break;
			default:
				$class = 'text';
				break;
		}
		return $class;
	}

	/**
	 * Check to see if a edit field template allready exists.
	 *
	 * This method may be overwritten to support a
	 * new implementation of {@link CodeGeneratorGUILayout::writeFieldEditTemplate()}
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEditTemplate()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return boolean true if view template allready exists
	 */
	public function hasFieldEdit(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		return $block->hasStatementRegex('/xsl\:template.*?name\=\"'.$this->getFieldEditID($column).'\".*?\n/ms');
	}

	/**
	 * Write edit field template.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @uses CodeGeneratorGUILayout::writeFieldEdit()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEditTemplate(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$call = new CodeGeneratorCodeBlockXMLElement('xsl:template');
		$call->setAttribute('name', $this->getFieldEditID($column));

		$param = $call->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:param'));
		$param->setAttribute('name', $this->getFieldEditName($column));

		$call->addComponent($this->writeFieldEdit($block, $column));
		return $call;
	}

	/**
	 * Write edit field container.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEditTemplate()
	 * @uses CodeGeneratorGUILayout::writeFieldEditLabel()
	 * @uses CodeGeneratorGUILayout::writeFieldEditInput()
	 * @uses CodeGeneratorGUILayout::writeFieldEditSelect()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEdit(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$container = new CodeGeneratorCodeBlockXMLElement('div');
		$container->setAttribute('class', 'edit-container '.$this->getTable()->getTableReadableVariableName().'-edit '.$this->getFieldEditID($column));
		$container->addComponent($this->writeFieldEditLabel($block, $column));

		if(!$column->isForeignKey()){
			$container->addComponent($this->writeFieldEditInput($block, $column));
		} else {
			$container->addComponent($this->writeFieldEditSelect($block, $column));
		}
		return $container;
	}

	/**
	 * Write edit field label.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEdit()
	 * @uses  CodeGeneratorGUILayout::writeFieldEditError()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEditLabel(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$label = new CodeGeneratorCodeBlockXMLElement('label');
		$label->setAttribute('for', $this->getFieldEditID($column));
		$label->addComponent(new CodeGeneratorCodeBlockXMLStatement($column->getFieldReadableVariableName()));
		$label->addComponent($this->writeFieldEditError($block, $column));
		return $label;
	}

	/**
	 * Write edit field error.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEditLabel()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEditError(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$if = new CodeGeneratorCodeBlockXMLElement('xsl:if');
		$if->setAttribute('test', '/page/settings/get/'.$column->getFieldReadableVariableName().'-error = true()');

		$error = $if->addComponent(new CodeGeneratorCodeBlockXMLElement('span'));
		$error->setAttribute('class', 'error');
		$error->addComponent(new CodeGeneratorCodeBlockXMLStatement('invalid '.$column->getFieldReadableVariableName()));
		return $if;
	}

	/**
	 * Write edit input element.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEdit()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEditInput(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$input = new CodeGeneratorCodeBlockXMLElement('input');
		$input->setAttribute('type', 'text');
		$input->setAttribute('name', $this->getFieldEditName($column));
		$input->setAttribute('id', $this->getFieldEditID($column));
		$input->setAttribute('value', '{$'.$this->getFieldEditName($column).'}');
		return $input;
	}

	/**
	 * Write edit select element.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldEdit()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldEditSelect(CodeGeneratorCodeBlockXML $block, CodeGeneratorColumn $column){
		$input = new CodeGeneratorCodeBlockXMLElement('select');
		$input->setAttribute('name', $this->getFieldEditName($column));
		$input->setAttribute('id', $this->getFieldEditID($column));
		return $input;
	}

	/**
	 * Check to see if a view template allready exists.
	 *
	 * This method may be overwritten to support a
	 * new implementation of {@link CodeGeneratorGUILayout::writeFieldViewTemplate()}
	 *
	 * @see CodeGeneratorGUILayout::writeFieldViewTemplate()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @return boolean true if view template allready has field
	 */
	final public function hasFieldView(CodeGeneratorCodeBlockXML $block){
		return $block->hasStatementRegex('/xsl\:template.*?name\=\"'.$this->getFieldViewName().'\".*?\n/ms');
	}

	/**
	 * Write view field template.
	 *
	 * write field template and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different. after template
	 * have been created, {@link CodeGeneratorGUILayout::writeFieldView()} is called
	 * in order to fill the template with it's content.
	 *
	 * @see CodeGeneratorGUILayout::hasFieldView()
	 * @uses CodeGeneratorGUILayout::writeFieldView()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldViewTemplate(CodeGeneratorCodeBlockXML $block){
		$call = new CodeGeneratorCodeBlockXMLElement('xsl:template');
		$call->setAttribute('name', $this->getFieldViewName());

		$param = $call->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:param'));
		$param->setAttribute('name', 'name');

		$param = $call->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:param'));
		$param->setAttribute('name', 'value');

		$call->addComponent($this->writeFieldView($block));
		return $call;
	}

	/**
	 * Write view field template content.
	 *
	 * write field and return {@link CodeGeneratorCodeBlockXMLElement}.
	 * This method can be overwritten if layout should be different.
	 *
	 * @see CodeGeneratorGUILayout::writeFieldViewTemplate()
	 * @param CodeGeneratorCodeBlockXML $block
	 * @param CodeGeneratorColumn $column
	 * @return CodeGeneratorCodeBlockXMLElement
	 */
	public function writeFieldView(CodeGeneratorCodeBlockXML $block){
		$container = new CodeGeneratorCodeBlockXMLElement('div');
		$container->setAttribute('class', $this->getFieldViewName());

		$span = $container->addComponent(new CodeGeneratorCodeBlockXMLElement('span'));
		$value = $span->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
		$value->setAttribute('select', '$name');

		$value = $container->addComponent(new CodeGeneratorCodeBlockXMLElement('xsl:value-of'));
		$value->setAttribute('select', '$value');

		return $container;
	}
}
?>