<?php
abstract class PageFactoryDOMXSLParseToken {
	const PATH_REWRITE_COMPLETE = 0;
	const PATH_REWRITE_PATH = 1;
	const PATH_REWRITE_TAGNAME = 2;

	abstract public function parse($page);

	protected function _xslRewriteParsePath($path, $type=self::PATH_REWRITE_COMPLETE){
		$path = trim($path);
		try {
			if($type == self::PATH_REWRITE_PATH){
				$path = dirname($path);
			}
			if($type == self::PATH_REWRITE_TAGNAME){
				return preg_replace('/.*?\/([A-Za-z0-9]+)\/*$/', '\\1', $path);
			}
			if(preg_match('/^[A-Za-z0-9\/]+$/', $path)){
				if($path != 'false' && $path != 'true'){
					if(preg_match('/^\/$/', $path)){
						$path = str_replace('/', 'PageFactoryDOMXSLCapsule::$data', $path);
					} else if(preg_match('/^\/page/', $path)){
						$path = str_replace('/page', 'PageFactoryDOMXSLCapsule::$data', $path);
					} else {
						$path = '$CURRENT/'.$path;
					}
					$path_parts = explode('/', $path);
					while (list($key,$val) = each($path_parts)) {
						if($key != 0){
							$path .= '[\''.$val.'\']';
						} else {
							$path = $val;
						}
					}
				}
			} else if ($path == '.') {
				return $path = '$CURRENT';
			} else {
				throw new BaseException('Template syntax error in path: '.$path,  E_USER_ERROR);
			}
		} catch (BaseException $e){
			$path = 'false';
			echo $e;
		}
		return $path;
	}
	protected function _xslRewriteCondition($cond){
		return $cond;
	}
}

class PageFactoryDOMXSLParseTokenTemplate extends PageFactoryDOMXSLParseToken {
	public function parse($page){
		$call_templates = array();
		$match_templates = array();
		preg_match_all('/\<c:template\s*(name|match)="(.*?)".*?\>(.*?)\<\/c:template\>[\s]*/s', $page, $matches);
		while (list($key,$val) = each($matches[3])) {
			if($matches[1][$key] == 'name'){
				$call_templates[$matches[2][$key]] = trim($val);
			} else {
				$match_templates[$matches[2][$key]] = trim($val);
			}
			$page = str_replace($matches[0][$key], '', $page);
		}
		$templates = $this->_xslCreateTemplateClass('PageFactoryDOMXSLCallTemplates', $call_templates);
		$templates .= $this->_xslCreateTemplateClass('PageFactoryDOMXSLMatchTemplates', $match_templates, false, true);

		$page = preg_replace('/(\<html.*?\>)/s', '\\0'."\n".trim($templates), $page);

		preg_match_all('/\<c:call-template.*?name="(.*?)".*?\>(.*?)\<\/c:call-template\>/s', $page, $matches);
		while (list($key, $val) = each($matches[1])) {
			$call = 'PageFactoryDOMXSLCallTemplates::'.$val;
			preg_match_all('/\<c:param\s*(.*?)\>(.*?)\<\/c:param\>/s', $matches[0][$key], $params);
			$param = 'array(';
			while (list($pkey, $pval) = each($params[1])) {
				$pname = preg_replace('/name="(.*?)".*?$/', '\\1', $pval);
				if(preg_match('/select=".*?"/', $pval)){
					$pval = preg_replace('/^.*?select="(.*?)".*?$/', '\\1', $pval);
					$pval = $this->_xslRewriteParsePath($pval);
					$param .= '\''.$pname.'\' => &'.$pval.', ';
				} else {
					$pval = trim($params[2][$pkey]);
					$param .= '\''.$pname.'\' => \''.addcslashes($pval, '\'').'\', ';
				}
			}
			$param .= ')';
			$call = '<?php PageFactoryDOMXSLCallTemplates::'.$val.'('.$param.'); ?>';
			$page = str_replace($matches[0][$key], $call, $page);
		}
		return $page;
	}
	private function _xslCreateTemplateClass($class, $templates, $addparam=true, $adddata=false){
		$class = '<?php class '.$class.' {'."\n";
		$class .= 'public function __call($name, $args){ }'."\n";
		while (list($key, $val) = each($templates)) {
			$param = '';
			if($adddata){
				$param = '$POSITION, $TAG, $CURRENT, ';
			}
			$param .= '$param=array()';

			$class .= 'static public function '.$key.'('.$param.'){'."\n";

			if($addparam){
				preg_match_all('/\<c:param name="(.*?)"\/*>(\<\/c:param\>)*/', $val, $param);
				$array = 'static $cparam = array(';

				while (list($pkey, $pval) = each($param[1])) {
					$array .= '\''.$pval.'\', ';
					$val = str_replace($param[0][$pkey], '', $val);
				}
				$array .= ');';
				$class .= $array."\n";
				$class .= 'while(list($key, $val) = each($param)){ if(in_array($key, $cparam)){ $$key = $val; } } unset($param);'."\n";
			}
			$class .= '?> '.trim($val).' <?php'."\n";
			$class .= '}'."\n";
		}
		$class .= '} ?>'."\n";
		return $class;
	}
}

class PageFactoryDOMXSLParseTokenDump extends PageFactoryDOMXSLParseToken {
	public function parse($page){
		// dump
		$page = preg_replace('/\<c:dump select="(.*?)"\/*\>(\<\/c:dump\>)*/ue',
		                     '\'<?php echo PageFactoryDOMXSLCapsule::dump(\'.$this->_xslRewriteParsePath(\'\\1\').\'); ?>\'', $page);
		return $page;
	}
}


class PageFactoryDOMXSLParseTokenControlStructure extends PageFactoryDOMXSLParseToken {
	public function parse($page){
		$page = preg_replace('/\<c:if.*?test="(.*?)".*?\>/sue',
		                     '\'<?php if(\'.$this->_xslRewriteCondition(\'\\1\').\'){ ?>\'', $page);
		$page = preg_replace('/\<\/c:if\>/s', '<?php } ?>', $page);
		return $page;
	}




		private function _xslRewriteOld(&$string){


		// if loop

		// for-each loop
		$string = preg_replace('/\<c:for-each select="(.*?)" as="(.*?)"\>/sue',
		                       '\'<?php while(list(\'.$this->_xslRewriteParseForeachAs(\'\\2\').\') = PageFactoryDOMXSLCapsule::each(\'.$this->_xslRewriteParsePath(\'\\1\').\')){ ?>\'', $string);
		$string = preg_replace('/\<\/c:for-each\>/s', '<?php } ?>', $string);

		// dump
		$string = preg_replace('/\<c:dump select="(.*?)"\/*\>(\<\/c:dump\>)*/ue',
		                       '\'<?php echo PageFactoryDOMXSLCapsule::dump(\'.$this->_xslRewriteParsePath(\'\\1\').\'); ?>\'', $string);

		// value-of
		$string = preg_replace('/\<c:value-of select="(.*?)"\/*>(\<\/c:value-of\>)*/sue',
		                       '\'<?php echo PageFactoryDOMXSLCapsule::valueOf(\'.$this->_xslRewriteParsePath(\'\\1\').\'); ?>\'', $string);

		// value-of
		$string = preg_replace('/\<c:copy-of select="(.*?)"\/*>(\<\/c:copy-of\>)*/sue',
		                       '\'<?php echo PageFactoryDOMXSLCapsule::copyOf(\'.$this->_xslRewriteParsePath(\'\\1\').\'); ?>\'', $string);

		// apply-templates
		$string = preg_replace('/\<c:apply-templates\s*select="(.*?)"\/*\>(\<\/c:apply-templates\>)*/sue',
		                       '\'<?php echo PageFactoryDOMXSLCapsule::applyTemplates(\'.$this->_xslRewriteParsePath(\'\\1\', PageFactoryDOMXSL::PATH_REWRITE_PATH).\', \\\'\'.$this->_xslRewriteParsePath(\'\\1\', PageFactoryDOMXSL::PATH_REWRITE_TAGNAME).\'\\\'); ?>\'', $string);
		$string = preg_replace('/\<c:apply-templates\/*\>(\<\/c:apply-templates\>)*/sue',
		                       '\'<?php echo PageFactoryDOMXSLCapsule::applyTemplates(\'.$this->_xslRewriteParsePath(\'.\').\'); ?>\'', $string);

		// Attribute tags, these are a litte harder to get, we require them to be inside a HTML
		// attribute, and the attribute can contain any number of matches and any other charecters
		// therefore first wee need to isolate all tags with attributes and then parse intag triggers
		preg_match_all('/(?<=(\<)).*?(?=(\>))/', $string, $matches);
		while (list($key, $val) = each($matches[0])) {
			if($val{0} != '/' && preg_match('/.*?=".*?\{.*?\}.*?"/', $val)){
				$val = preg_replace('/\{(.*?)\}/e',
				                    '\'<?php echo PageFactoryDOMXSLCapsule::valueOf(\'.$this->_xslRewriteParsePath(\'\\1\').\'); ?>\'', $val);
				$string = str_replace($matches[1][$key].$matches[0][$key].$matches[2][$key],
				                      $matches[1][$key].$val.$matches[2][$key], $string);
			}
		}

	}


}


?>