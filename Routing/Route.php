<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib route description class.
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
 * @author Steffen Sørensen <steffen@sublife.dk>
 * @copyright Copyright (c) 2012 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version ($Id$)
 * @see Corelib\Base\Routing\Registry
 */
namespace Corelib\Base\Routing;

class Route {
	private $prefix = false;
	private $url = false;
	private $include = false;
	private $expression = false;
	private $callback_class = false;
	private $callback_method = false;
	private $callback_args = array();
	private $callback_condition = false;
	private $callback_condition_args = array();

	public function __construct(\stdClass $options, array $macros=array()){
		 $this->prefix = (isset($options->prefix) ? $options->prefix : false);
		 $this->url = (isset($options->url) ? $options->url : false);
		 $this->include = (isset($options->include) ? $options->include : false);
		 $this->expression = (isset($options->expression) ? $options->expression : false);
		 $this->callback_class = (isset($options->callback_class) ? $options->callback_class : 'WebPage');
		 $this->callback_method = (isset($options->callback_method) ? $options->callback_method : 'build');
		 $this->callback_args = (isset($options->callback_args) ? $options->callback_args : array());
		 $this->callback_condition = (isset($options->callback_condition) ? $options->callback_condition : false);
		 $this->callback_condition_args = (isset($options->callback_condition_args) ? $options->callback_condition_args : array());

		if(sizeof($macros) > 0){
			foreach($macros as $key => $val){
				$this->include = str_replace('${'.$key.'}', $val, $this->include);
				$this->callback_class = str_replace('${'.$key.'}', $val, $this->callback_class);
				$this->callback_method = str_replace('${'.$key.'}', $val, $this->callback_method);
				$this->callback_condition = str_replace('${'.$key.'}', $val, $this->callback_condition);
				foreach($this->callback_condition_args as &$arg){
					$arg = str_replace('${'.$key.'}', $val, $arg);
				}
				foreach($this->callback_args as &$arg){
					$arg = str_replace('${'.$key.'}', $val, $arg);
				}
			}
		}
	}

	public function getPrefix(){
		return $this->prefix;
	}

	public function getUrl(){
		return $this->url;
	}
	public function getInclude(){
		return $this->include;
	}
	public function getExpression(){
		return $this->expression;
	}
	public function getCallbackClass(){
		return $this->callback_class;
	}
	public function getCallbackMethod(){
		return $this->callback_method;
	}
	public function getCallbackArgs(){
		return $this->callback_args;
	}
	public function getCallbackCondition(){
		return $this->callback_condition;
	}
	public function getCallbackConditionArgs(){
		return $this->callback_condition_args;
	}
}