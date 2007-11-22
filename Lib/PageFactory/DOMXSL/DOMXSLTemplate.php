<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * PageFactory DOMXSL Template
 *
 * <i>No Description</i>
 *
 * LICENSE: This source file is subject to version 1.0 of the
 * Bravura Distribution license that is available through the
 * world-wide-web at the following URI: http://www.bravura.dk/licence/corelib_1_0/.
 * If you did not receive a copy of the Bravura License and are
 * unable to obtain it through the web, please send a note to
 * license@bravura.dk so we can mail you a copy immediately.
 *
 *
 * @author Steffen Sorensen <steffen@bravura.dk>
 * @copyright Copyright (c) 2006 Bravura ApS
 * @license http://www.bravura.dk/licence/corelib_1_0/
 * @package corelib
 * @subpackage Base
 * @link http://www.bravura.dk/
 * @version 1.0.0 ($Id$)
 * @filesource
 */


if(!defined('DOMXSL_TEMPLATE_XSL_PATH')){
	define('DOMXSL_TEMPLATE_XSL_PATH', CURRENT_WORKING_DIR.'share/xsl/');
}

class PageFactoryDOMXSLTemplate extends PageFactoryWebAbstractTemplate {
	private $xsl_templates = array();

	private $xsl_core = null;

	private $xml_version = '1.0';
	private $xml_encoding = 'UTF-8';

	private $custom_cache_string = null;

	const TEMPLATE_ENGINE = 'PageFactoryDOMXSL';

	const XSL_NAMESPACE_URI = 'http://www.w3.org/1999/XSL/Transform';

	public function __construct($xslcore = null){
		parent::__construct();
		if(is_null($xslcore)){
			$xslcore = DOMXSL_TEMPLATE_XSL_PATH.'base/core.xsl';
		}else {
			$xslcore = DOMXSL_TEMPLATE_XSL_PATH.$xslcore;
		}
		try {
			if(!is_file($xslcore)){
				throw new BaseException('No such file or directory: '.$xslcore);
			}
		} catch (BaseException $e){
			echo $e;
			exit;
		}
		$this->xsl_core = $xslcore;
	}

	public function getCoreXSLT(){
		return $this->xsl_core;
	}
	public function addTemplate($template_file){
		try {
			StrictTypes::isString($template_file);
		} catch (BaseException $e){
			echo $e;
		}
		if($template_file{0} == '/' || preg_match('/^[a-zA-Z]:/', $template_file)){
			$template = $template_file;
		} else {
			try {
				if(!$template = realpath(DOMXSL_TEMPLATE_XSL_PATH.$template_file)){
					throw new BaseException('XSL Template file not found: '.DOMXSL_TEMPLATE_XSL_PATH.$template_file, E_USER_ERROR);
				}
			} catch (BaseException $e){
				echo $e;
				exit;
			}
		}
		$this->xsl_templates[] = $template;
	}
	public function buildCoreTemplate(DOMDocument $xsl){
		while(list(,$val) = each($this->xsl_templates)){
			$XSLinclude = $xsl->documentElement->appendChild($xsl->createElementNS(self::XSL_NAMESPACE_URI, 'xsl:include'));
			$XSLinclude->setAttribute('href', $val);
		}
	}

	public function setXMLVersion($version){
		$this->xml_version = $version;
		try {
			StrictTypes::isString($encoding);
		} catch (BaseException $e){
			echo $e;
		}
	}
	public function setXMLEncoding($encoding){
		try {
			StrictTypes::isString($encoding);
		} catch (BaseException $e){
			echo $e;
		}
		$this->xml_encoding = $encoding;
	}
	public function setPageCacheString($string){
		try {
			StrictTypes::isString($string);
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		$this->custom_cache_string = $string;
		return true;
	}


	public function getPageCacheString(){
		if(is_null($this->custom_cache_string)){
			$this->custom_cache_string = sha1($this->getScriptUri().serialize($this->xsl_templates).$this->xsl_core);
		}
		return $this->custom_cache_string;
	}


	public function getSupportedTemplateEngineName(){
		return self::TEMPLATE_ENGINE;
	}
	public function getXMLVersion(){
		return $this->xml_version;
	}
	public function getXMLEncoding(){
		return $this->xml_encoding;
	}
}
?>