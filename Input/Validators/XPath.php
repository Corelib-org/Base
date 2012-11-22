<?php
namespace Corelib\Base\Input\Validators;
use Corelib\Base\Input\Validator;

/**
 * InputValidatorXpath validator class.
 *
 * Use this class to validate content against a xml element- or document based
 * on a Xpath query, if the query evals returns 1 or more results it will
 * eval as true.
 *
 * @category corelib
 * @package Base
 * @subpackage InputHandler
 */
class XPath implements Validator {


	//*****************************************************************//
	//************* InputValidatorXpath class properties **************//
	//*****************************************************************//
	/**
	 * Xpath instance.
	 *
	 * @var DOMXpath
	 */
	private $xpath = null;

	/**
	 * Xpath query
	 *
	 * @var string
	 */
	private $query = null;


	//*****************************************************************//
	//************* InputValidatorXpath class methods *****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @param mixed $xml DOMElement or DOMNode to use
	 * @param string $xpath xpath query to check against, a sprinf format can be used with one argument which refers to the input value
	 * @return void
	 * @throws BaseException if first argument is invalid.
	 * @internal
	 */
	public function __construct($xml, $query){
		$this->query = $query;
		if($xml instanceof DOMDocument){
			$this->xpath = new DOMXPath($xml);
		} else if($xml instanceof DOMElement){
			$doc = new DOMDocument();
			$doc->appendChild($doc->importNode($xml, true));
			$this->xpath = new DOMXPath($doc);
		} else {
			throw new BaseException('Invalid argument, first argument must be either DOMDocument or DOMElement', E_USER_ERROR);
		}
	}

	/**
	 * Validate content against xpath query.
	 *
	 * @see InputValidator::validate()
	 * @return boolean true i content is valid, else return false
	 */
	public function validate($content){
		$query = $this->xpath->query(sprintf($this->query, $content));
		if($query->length > 0){
			return true;
		} else {
			return false;
		}
	}
}
?>