<?php
namespace Corelib\Base\PageFactory;
use DOMDocument as NativeDOMDocument, Corelib\Base\Tools\XML;
/**
 * Page factory DOMDocument.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 */
class DOMDocument extends NativeDOMDocument {


	//*****************************************************************//
	//************ PageFactoryDOMDocument class methods ***************//
	//*****************************************************************//
	/**
	 * Create new DOMElement
	 *
	 * Return new instance of DOMElement linked to DOMDocument.
	 * The difference from this method to the original is that
	 * it will always do {@link XMLTools::escapeXMLCharacters()}
	 * on the content of the node.
	 *
	 * @see http://www.php.net/manual/en/domdocument.createelement.php
	 */
	public function createElement($name, $content=null){
		if(!is_null($content)){
			return parent::createElement($name, XML::escapeXMLCharacters($content));
		} else {
			return parent::createElement($name);
		}
	}
}
?>