<?php
namespace Corelib\Base\ObjectRelationalMapping\DataAccess;

use \Corelib\Base\ObjectRelationalMapping\Metadata\Parser;

abstract class ObjectList extends \Corelib\Base\Database\DataAccessObject {
	/**
	 * Get the list according to the current criteria.
	 *
	 * @param \DatabaseListHelperFilter $filter
	 * @param \DatabaseListHelperOrder $order
	 * @param integer $offset
	 * @param integer $limit
	 * @return Query
	 */
	abstract public function getList(Parser $metadata, \DatabaseListHelperFilter $filter, \DatabaseListHelperOrder $order, $offset=null, $limit=null);

	/**
	 * Count the number of objects according to the current listing criteria.
	 *
	 * @param \DatabaseListHelperFilter $filter
	 * @return integer number of objects in the list
	 */
	abstract public function getListCount(Parser $metadata, \DatabaseListHelperFilter $filter);

}
?>