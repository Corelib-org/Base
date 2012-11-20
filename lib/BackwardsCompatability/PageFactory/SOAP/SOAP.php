<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Page factory DOMXSL Template engine.
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
 * @todo impliment XSLT profiling and developer toolbar item
 */
ini_set("soap.wsdl_cache_enabled", "0");

//*****************************************************************//
//******************* PageFactoryDOMXSL class *********************//
//*****************************************************************//
/**
 * Page Factory DOMXSL template engine.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
class PageFactorySOAP extends PageFactoryTemplateEngine {

	const SOAP = 'http://schemas.xmlsoap.org/wsdl/soap/';
	const WSDL = 'http://schemas.xmlsoap.org/wsdl/';
	const XSD = 'http://www.w3.org/2001/XMLSchema';

	private $tns = null;
	private $base = null;
	private $name = null;
	private $root = null;
	private $handler = null;
	private $server = null;

	private $operations = array();

	public function __construct(){
		parent::__construct();
		Base::getInstance()->loadClass('WebInteralLoopbackStream');
	}

	public function addPageContent(Output $content){

	}

	public function addPageSettings(Output $setting){

	}

	public function draw(){
		$input = InputHandler::getInstance();
		if($input->validateGet('wsdl', new InputValidatorIsSet())){
			header('Content-Type: text/xml');
			echo $this->wsdl->saveXML();
		} else {
			$wsdl = 'internal:/'.$_SERVER['REQUEST_URI'].'?wsdl';
			$this->server = $this->handler->setServer(new SoapServer($wsdl));
			$this->server->handle();
		}
	}

	public function build(PageBase $page, $callback=null){
		$this->handler = new PageFactorySOAPRequestHandler($this, $page);
		$this->root = 'http://'.$_SERVER['SERVER_NAME'].'/';
		$this->base = 'http://'.$_SERVER['SERVER_NAME'].preg_replace('/(.*?)(\?.*?)$/', '\\1', $_SERVER['REQUEST_URI']);
		$this->tns = $this->base.'?wsdl';
		$this->name = preg_replace('/(.*?)(\?.*?)$/', '\\1', $_SERVER['REQUEST_URI']);
		$this->name = preg_replace('/\//', '', $this->name);

		$this->wsdl = new DOMDocument('1.0', 'utf-8');
		$this->wsdl->formatOutput = true;

		$this->wsdl->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:definitions'));
		$this->wsdl->documentElement->setAttribute('targetNamespace', $this->tns);
		$this->wsdl->documentElement->setAttribute('name', $this->name);
		$this->wsdl->documentElement->setAttribute('xmlns:tns', $this->tns);
		$this->wsdl->documentElement->setAttribute('xmlns:xsd', PageFactorySOAP::XSD);
		$this->wsdl->documentElement->setAttribute('xmlns:soap', PageFactorySOAP::SOAP);

		$service = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:service'));
		$service->setAttribute('name', $this->name);

		$port = $service->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:port'));
		$port->setAttribute('binding', 'tns:'.$this->name.'SOAP');
		$port->setAttribute('name', $this->name.'SOAP');
		$address = $port->appendChild($this->wsdl->createElementNS(PageFactorySOAP::SOAP, 'soap:address'));
		$address->setAttribute('location', $this->base);

		$schema = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:schema'));
		$schema->setAttribute('targetNamespace', $this->tns);

		$porttype = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:portType'));
		$porttype->setAttribute('name', $this->name);


		$binding = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:binding'));
		$binding->setAttribute('name', $this->name.'SOAP');
		$binding->setAttribute('type', 'tns:'.$this->name);

		$sbinding = $binding->appendChild($this->wsdl->createElementNS(PageFactorySOAP::SOAP, 'soap:binding'));
		$sbinding->setAttribute('style', 'document');
		$sbinding->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');

		$reflection  = new ReflectionClass($page);

		foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method){

			if($method->getDeclaringClass()->getName() == get_class($page)){
				$doc = $method->getDocComment();
				$parameters = array();
				if(preg_match_all('/@param\s+(.*?)\s+\$(.*?)\s+(.*)$/m', $doc, $matches)){
					foreach($matches[2] as $id => $parameter){
						$parameters[$parameter] = array('type'=>$matches[1][$id], 'description'=>$matches[3][$id]);
					}
				}

				$this->operations[$method->getName()] = $parameters;

				$request = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:message'));
				$request->setAttribute('name', $method->getName());
				$part = $request->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:part'));
				$part->setAttribute('name', $method->getName());
				$part->setAttribute('element', 'tns:'.$method->getName());

				$response = $this->wsdl->documentElement->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:message'));
				$response->setAttribute('name', $method->getName().'Response');

				$part = $response->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:part'));
				$part->setAttribute('name', $method->getName().'Response');

				if(preg_match('/@return\s+(.*?)\s+(.*?)$/m', $doc, $matches)){
					switch($matches[1]){
						case 'string':
							$part->setAttribute('type', 'xsd:string');
							break;
						case 'boolean':
							$part->setAttribute('type', 'xsd:boolean');
							break;
						case 'integer':
							$part->setAttribute('type', 'xsd:integer');
							break;
						case 'float':
							$part->setAttribute('type', 'xsd:float');
							break;
						default:
							throw new SoapFault('unknown datatype specified in @return: '.$matches[1]);
							$part = $response->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:part'));
							$part->setAttribute('name', $method->getName());
							$part->setAttribute('element', 'tns:'.$method->getName().'Response');
							$sresponse = $schema->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:element'));
							$sresponse->setAttribute('name', $method->getName().'Response');
							$sresponse->setAttribute('minOccurs', '1');
							$sresponse->setAttribute('maxOccurs', '1');
							$sresponse->setAttribute('type', 'xsd:boolean');
					}
				} else {
					$part = $response->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:part'));
					$part->setAttribute('name', $method->getName().'Response');
					$part->setAttribute('type', 'xsd:boolean');
				}

				$srequest = $schema->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:element'));
				$srequest->setAttribute('name', $method->getName());
				$srequest = $srequest->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:complexType'));
				$srequest = $srequest->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:sequence'));

				foreach($method->getParameters() as $parameter){
					if(isset($parameters[$parameter->getName()])){
						$type = $srequest->appendChild($this->wsdl->createElementNS(PageFactorySOAP::XSD, 'xsd:element'));
						if(isset($parameters[$parameter->getName()]['description'])){
							$type->appendChild($this->wsdl->createElement('documentation', $parameters[$parameter->getName()]['description']));
						}
						switch($parameters[$parameter->getName()]['type']){
							case 'integer':
								$type->setAttribute('minOccurs', '1');
								$type->setAttribute('maxOccurs', '1');
								$type->setAttribute('name', $parameter->getName());
								$type->setAttribute('type', 'xsd:integer');
								break;
							case 'string':
								$type->setAttribute('minOccurs', '1');
								$type->setAttribute('maxOccurs', '1');
								$type->setAttribute('name', $parameter->getName());
								$type->setAttribute('type', 'xsd:string');
								break;
							default:
								throw new Exception('Parameter '.$parameter->getName().' in '.$method->getName().' has a unknown datatype: '.$parameters[$parameter->getName()]['type'].' .');
						}
					} else {
						throw new Exception('Parameter '.$parameter->getName().' in '.$method->getName().' is missing documentation.');
					}
				}

				$operation = $porttype->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:operation'));
				$operation->setAttribute('name', $method->getName());
				$input = $operation->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:input'));
				$input->setAttribute('message', 'tns:'.$method->getName());
				$output = $operation->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:output'));
				$output->setAttribute('message', 'tns:'.$method->getName().'Response');

				$operation = $binding->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:operation'));
				$operation->setAttribute('name', $method->getName());

				$soperation = $operation->appendChild($this->wsdl->createElementNS(PageFactorySOAP::SOAP, 'soap:operation'));
				$soperation->setAttribute('soapAction', $this->base.'?'.$method->getName());
				$input = $operation->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:input'));
				$input = $input->appendChild($this->wsdl->createElementNS(PageFactorySOAP::SOAP, 'soap:body'));
				$input->setAttribute('use', 'literal');
				$output = $operation->appendChild($this->wsdl->createElementNS(PageFactorySOAP::WSDL, 'wsdl:output'));
				$output = $output->appendChild($this->wsdl->createElementNS(PageFactorySOAP::SOAP, 'soap:body'));
				$output->setAttribute('use', 'literal');
			}
		}
	}

	public function getOperation($operation){
		if(isset($this->operations[$operation])){
			return $this->operations[$operation];
		} else {
			$this->server->fault(E_USER_ERROR, 'Operation does not exists: '.$operation);
		}
	}
}

class PageFactorySOAPRequestHandler {
	private $page = null;
	private $soap = null;
	private $server = null;

	public function __construct(PageFactorySOAP $soap, SOAPPageBase $page){
		$this->page = $page;
		$this->soap = $soap;
	}

	public function setServer(SoapServer $server){
		$server->setObject($this);
		$this->server = $server;
		return $server;
	}

	public function __call($operation, $args){
		if($parameters = $this->soap->getOperation($operation)){
			foreach($parameters as $name => $description){
				if(isset($args[0]->$name)){
					switch($description['type']){
						case 'string':
							$callback[] = (string) trim($args[0]->$name);
							break;
						default:
							$this->server->fault(E_USER_ERROR, 'Unknown data type: ', $description['type']);
					}
				} else {
					$callback[] = null;
				}
			}
		}
		foreach($args[0] as $name => $value){
			if(!isset($parameters[$name])){
				$this->server->fault(E_USER_ERROR, 'message does not exists: '.$name);
			}
		}
		$this->page->setServer($this->server);
		return call_user_func_array(array($this->page, $operation), $callback);
	}
}


class SOAPPageBase extends PageBase {

	private $server = null;

	public function setServer(SoapServer $server){
		$this->server = $server;
	}

	public function fault($code, $message){
		$this->server->fault($code, $message);
	}
}