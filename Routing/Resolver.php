<?php
namespace Corelib\Base\Routing;


class Resolver {
	public function makeExpression($expression){
		return $expression;
	}

	public function makeClassName($class){
		return $class;
	}

	public function makeMethodName($method){
		return $method;
	}
}

?>