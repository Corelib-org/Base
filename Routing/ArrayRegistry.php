<?php
namespace Corelib\Base\Routing;

class ArrayRegistry extends Registry {
	private $pages = array();

	public function __construct(array &$pages){
		parent::__construct();

		foreach($pages as $key => $val){
			$obj = new \stdClass();

			if(is_string($val)){
				$page = $val;
				$val = array();
				$val['page'] = $page;
			}

			if(is_string($key)){
				$obj->url = $key;
				$obj->prefix = $key;
			} else {
				if(isset($val['prefix'])){
					$obj->prefix = $val['prefix'];
				}
			}

			if(isset($val['exec'])){
				if(isset($val['type'])){
					if($val['type'] == 'regex'){
						$resolver = new Resolver();
					}

					if(!isset($val['class'])){
						$val['class'] = 'WebPage';
					}

					if(!isset($val['method'])){
						if(!isset($val['exec'])){
							$val['method'] = 'build';
						} else {
							$val['method'] = preg_replace('/(\\\([0-9]+))/', '${\\2}', $val['exec']);
							$val['method'] = preg_replace('/^(.*?)\(.*$/', '\\1', $val['method']);
						}
					}
					if(!isset($val['args'])){
						$val['args'] = preg_replace('/^(.*?)\((.*?)\)$/', '\\2', $val['exec']);
						$val['args'] = preg_replace('/(\\\([0-9]+))/', '${\\2}', $val['args']);
						$val['args'] = preg_split('/,\s*/', $val['args']);
					}
					$obj->expression = $resolver->makeExpression($val['expr']);
					$obj->callback_class = $resolver->makeClassName($val['class']);
					$obj->callback_method = $resolver->makeMethodName($val['method']);
					$obj->callback_args = $val['args'];
				} else {
					$obj->callback_class = 'WebPage';
					$obj->callback_method = $val['exec'];
				}
			}
			if(isset($val['page'])){
				$obj->include = $val['page'];
			}
			if(isset($val['precondition'])){
				$obj->callback_condition = 'eval';
				$obj->callback_condition_args = array($val['precondition']);
			}

			$this->addRoute(new Route($obj));

		}
	}
}