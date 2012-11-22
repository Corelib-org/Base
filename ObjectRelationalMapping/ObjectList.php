<?php
namespace Corelib\Base\ObjectRelationalMapping;

use Corelib\Base\PageFactory\Output, Corelib\Base\ServiceLocator\Locator, Corelib\Base\Converters\Converter;

abstract class ObjectList extends ObjectBase implements Output {

	//*****************************************************************//
	//**************************** Properties *************************//
	//*****************************************************************//
	protected $dao = null;

	/**
	 * @var \DatabaseListHelperOrder
	 */
	protected $order = null;

	/**
	 * @var \DatabaseListHelperFilter
	 */
	protected $filter = null;

	/**
	 * @var boolean
	 * @internal
	 */
	private $paging = false;
	/**
	 * @var integer current page
	 */
	private $paging_page = 1;
	/**
	 * @var integer
	 */
	private $limit = null;
	/**
	 * @var integer
	 * @internal
	 */
	private $offset = null;

	private $metadata = null;


	public function __construct($class){
		$this->metadata = new Metadata\Parser($class);
		$this->order = new \DatabaseListHelperOrder();
		$this->filter = new \DatabaseListHelperFilter();

		$this->dao = Locator::get('Corelib\Base\Database\Connection')->getDAO(
			get_class($this)
		);
	}


	//*****************************************************************//
	//************************ List parameters ************************//
	//*****************************************************************//
	/**
	 * Enable paging and set current page.
	 *
	 * @uses ObjectList::$paging
	 * @uses ObjectList::$paging_page
	 * @param integer $page current page
	 */
	public function setPage($page=1){
		$this->paging = true;
		$this->paging_page = $page;
	}

	/**
	 * Set limit.
	 *
	 * If paging is enabled with {@link ObjectList::setPage()}
	 * the limit is set of the amount of objects on one page. If not
	 * enabled normal limit is used
	 *
	 * @uses ObjectList::$limit
	 * @param integer $limit
	 */
	public function setLimit($limit){
		$this->limit = $limit;
	}

	/**
	 * Set offset.
	 *
	 * If paging is enabled this has no effect and will be overwritten,
	 * otherwise this set the normal offset
	 *
	 * @uses ObjectList::$offset
	 * @param integer $offset
	 */
	public function setOffset($offset){
		$this->offset = $offset;
	}

	/**
	 * Count the number of object according to the current listing criteria.
	 *
	 * @uses ObjectList::_getDao()
	 * @uses ObjectList::$dao
	 * @uses ObjectList::$filter
	 * @uses Corelib\Base\ObjectRelationalMapping\DataAccess\ObjectList::getListCount()
	 * @return integer number of objects in the list
	 * @api
	 */
	public function getCount() {
		return $this->dao->getListCount($this->metadata, $this->filter);
	}

	/**
	 * Iterate over each result.
	 *
	 * @uses ObjectList::$dao
	 * @uses Corelib\Base\ObjectRelationalMapping\DataAccess\ObjectList::getList()
	 * @uses ObjectList::$filter
	 * @uses ObjectList::$order
	 * @uses ObjectList::$offset
	 * @uses ObjectList::$limit
	 * @uses \Query::dataSeek()
	 * @return Object
	 * @api
	 */
	public function each(){
		static $res = null;
		if(is_null($res)){
			$res = $this->dao->getList($this->metadata, $this->filter, $this->order, $this->offset, $this->limit);
		}
		if($out = $res->fetchArray()){
			$class = $this->metadata->getReflection()->getName();
			$item = new $class();
			return $item;
		} else {
			$res->dataSeek(0);
			$res = null;
			return false;
		}
	}

	public function __call($method, $args){
		if($property = $this->_getPropertyFromMethod($method, 'set', 'Converter')){
			if($args[0] instanceof Converter){
				return $this->_setConverter($property, $args[0]);
			} else {
				throw new Exception('Call to '.get_class($this).'::'.$method.'() expected first argument to be instance of class \Converter');
			}
		}
	}

	public function getXML(\DOMDocument $xml){
		$list = $xml->createElement($this->_convertMethodToProperty(get_class($this), '-'));
		$list->appendChild($this->metadata->getXML($xml));

		if($this->paging){
			$count = $this->getCount();
			$this->setOffset($this->limit * ($this->paging_page - 1));
			$list->appendChild(\XMLTools::makePagerXML($xml, $count, $this->limit, $this->paging_page));
		}

		$class = $this->metadata->getName();

		$converters = false;
		foreach($this->_getConverters() as $key => $val){
			$converters['set'.$this->_convertPropertyToMethod($key).'Converter'] = $val;
		}

		$res = $this->dao->getList($this->metadata, $this->filter, $this->order, $this->offset, $this->limit);
		while ($out = $res->fetchAssoc()) {
			$item = new $class($out, $this->metadata);
			if($converters){
				foreach($converters as $method => $converter){
					$item->$method($converter);
				}
			}
			$list->appendChild($item->getXML($xml));
		}


		return $list;
	}

}

?>