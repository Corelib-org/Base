<?php
namespace Corelib\Base\Converters;


/**
 * Converter chain.
 *
 * The converter chain class allows for multiple converters
 * to be treated as one. meaning all conversion will be made in
 * the order there are added using {@link ConverterChain::addConveter()}.
 *
 * @category corelib
 * @package Base
 * @subpackage Converters
 */
class Chain implements Converter {


	//*****************************************************************//
	//************** ConverterChain class properties ******************//
	//*****************************************************************//
	/**
	 * @var array list of converters
	 * @internal
	 */
	private $converters = array();


	//*****************************************************************//
	//**************** ConverterChain class methods *******************//
	//*****************************************************************//
	/**
	 * Add converter to converter chain.
	 *
	 * @param Converter $converter
	 * @return boolean true on success, else return false
	 */
	public function addConveter(Converter $converter){
		$this->converters[] = $converter;
		return true;
	}

	/**
	 * Convert data.
	 *
	 * @see Converter::convert()
	 * @internal
	 */
	public function convert($data){
		foreach ($this->converters as $converter){
			$data = $converter->convert($data);
		}
		return $data;
	}
}
?>