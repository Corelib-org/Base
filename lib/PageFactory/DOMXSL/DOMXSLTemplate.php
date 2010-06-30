<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page factory DOMXSL Template.
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
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2010 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id$)
 */

//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('DOMXSL_TEMPLATE_XSL_PATH')){
	/**
	 * XSLT template path.
	 *
	 * Where to look for the xsl templates
	 *
	 * @var string directory
	 */
	define('DOMXSL_TEMPLATE_XSL_PATH', CURRENT_WORKING_DIR.'share/xsl/');
}


//*****************************************************************//
//*************** PageFactoryDOMXSLTemplate class *****************//
//*****************************************************************//
/**
 * DOMXSL Page factory template.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
class PageFactoryDOMXSLTemplate extends PageFactoryWebAbstractTemplate {


	//*****************************************************************//
	//********** PageFactoryDOMXSLTemplate class properties ***********//
	//*****************************************************************//
	/**
	 * @var array xsl templates.
	 * @internal
	 */
	private $xsl_templates = array();

	/**
	 * @var string xsl core filename
	 * @internal
	 */
	private $xsl_core = null;

	/**
	 * @var string XML Version
	 * @internal
	 */
	private $xml_version = '1.0';

	/**
	 * @var string XML encoding
	 * @internal
	 */
	private $xml_encoding = 'UTF-8';

	/**
	 * @var array list of php functions exported to xslt
	 * @internal
	 */
	private $registered_php_functions = false;

	/**
	 * @var Converter Output converter
	 * @internal
	 */
	private $output_converter = null;


	//*****************************************************************//
	//********** PageFactoryDOMXSLTemplate class constants ************//
	//*****************************************************************//
	/**
	 * @var string Supported template engine name
	 * @internal
	 */
	const TEMPLATE_ENGINE = 'PageFactoryDOMXSL';

	/**
	 * @var XSL Namespace URI
	 * @internal
	 */
	const XSL_NAMESPACE_URI = 'http://www.w3.org/1999/XSL/Transform';


	//*****************************************************************//
	//*********** PageFactoryDOMXSLTemplate class methods *************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param string $xslcore xsl core filename
	 * @return void
	 */
	public function __construct($xslcore = null){
		parent::__construct();
		if(is_null($xslcore)){
			$xslcore = DOMXSL_TEMPLATE_XSL_PATH.'base/core.xsl';
		} else if ($xslcore{0} != '/'){
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
		$this->setContentType('application/xhtml+xml; charset=utf-8');
	}

	/**
	 * Get XSLT core.
	 *
	 * @return string filename
	 */
	public function getCoreXSLT(){
		return $this->xsl_core;
	}

	/**
	 * Set XSLT Core.
	 *
	 * @param string $xslcore xsl core filename
	 * @return void
	 */
	public function setCoreXSLT($xslcore){
		$this->xsl_core = $xslcore;
	}

	/**
	 * Get registered PHP functions.
	 *
	 * @return array
	 */
	public function getRegisteredPHPFunctions(){
		return $this->registered_php_functions;
	}

	/**
	 * Add XSLT template.
	 *
	 * @param string $template_file XSLT Template
	 * @return boolean true on success, else return false
	 */
	public function addTemplate($template_file){
		if($template_file{0} ==	'/' || preg_match('/^[a-zA-Z]:/', $template_file)){
			$template = $template_file;
		} else {
			if(!$template = realpath(DOMXSL_TEMPLATE_XSL_PATH.$template_file)){
				trigger_error('XSL Template file not found: '.DOMXSL_TEMPLATE_XSL_PATH.$template_file, E_USER_ERROR);
				return false;
			}
		}
		$this->xsl_templates[] = $template;
		return true;
	}

	/**
	 * Build core templates.
	 *
	 * @param DOMDocument $xsl
	 * @return boolean true on success, else return false
	 * @internal
	 */
	public function buildCoreTemplate(DOMDocument $xsl){
		while(list(,$val) = each($this->xsl_templates)){
			$XSLinclude = $xsl->documentElement->appendChild($xsl->createElementNS(self::XSL_NAMESPACE_URI, 'xsl:include'));
			$XSLinclude->setAttribute('href', $val);
		}
		return true;
	}

	/**
	 * Register PHP functions.
	 *
	 * Register a number of php functions which
	 * should be exportet to XSLT.
	 *
	 * @param array $functions list of php function
	 * @return boolean true on success, else return false
	 */
	public function registerPHPFunctions(array $functions){
		$this->registered_php_functions = $functions;
		return true;
	}

	/**
	 * Set XML Version
	 *
	 * @param string $version
	 * @return boolean true on success, else return false
	 */
	public function setXMLVersion($version){
		$this->xml_version = $version;
		return true;
	}

	/**
	 * Set XML Character encoding.
	 *
	 * @param $encoding
	 * @return boolean true on success, else return false
	 */
	public function setXMLEncoding($encoding){
		$this->xml_encoding = $encoding;
		return true;
	}

	/**
	 * Set content output converter.
	 *
	 * This converter will be applied to the generated page
	 * before outputting to the browser.
	 *
	 * @param Converter $converter
	 * @return boolean true on success, else return false
	 */
	public function setOutputConverter(Converter $converter){
		$this->output_converter = $converter;
		return true;
	}

	/**
	 * Get content output converter.
	 *
	 * @return Converter if set, else return null
	 * @internal
	 */
	public function getOutputConverter(){
		return $this->output_converter;
	}

	/**
	 * Get supported template engine name.
	 *
	 * @see PageFactoryTemplate::getSupportedTemplateEngineName()
	 * @internal
	 */
	public function getSupportedTemplateEngineName(){
		return self::TEMPLATE_ENGINE;
	}

	/**
	 * Get XML Version.
	 *
	 * @return string XML Version
	 * @see PageFactoryDOMXSLTemplate::setXMLVersion()
	 */
	public function getXMLVersion(){
		return $this->xml_version;
	}

	/**
	 * Get XML character encoding.
	 *
	 * @return string XML encoding
	 * @see PageFactoryDOMXSLTemplate::setXMLEncoding()
	 */
	public function getXMLEncoding(){
		return $this->xml_encoding;
	}
}
?>