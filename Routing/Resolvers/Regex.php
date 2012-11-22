<?php
namespace Corelib\Base\Routing\Resolvers;

use Corelib\Base\Routing\Resolver;

class Regex extends Resolver {
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