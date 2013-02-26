<?php
namespace Corelib\Base\ObjectRelationalMapping\DataAccess\MySQLi;

use \Corelib\Base\ObjectRelationalMapping\Exception;
use \Corelib\Base\ObjectRelationalMapping\Metadata\Parser;
use Corelib\Base\Database\MySQLi\Query;

abstract class ObjectList extends \Corelib\Base\ObjectRelationalMapping\DataAccess\ObjectList {

	//*****************************************************************//
	//******************** Data retrieval methods *********************//
	//*****************************************************************//
	/**
	 * @see DAO_SignupList::getList()
	 * @return MySQLiQuery
	 */
	public function getList(Parser $metadata, \DatabaseListHelperFilter $filter, \DatabaseListHelperOrder $order, $offset=null, $limit=null, $view=null){
		/* Order statement */
		$order = \MySQLiTools::prepareOrderStatement($order);
		/* Order statement end */
		if(!$order){
			$order = '';
		} else {
			$order = 'ORDER BY '.$order;
		}

		$filters = $this->_prepareFilterStatements($filter);
		$join = $filters['join'];
		$where = $filters['where'];
		// $columns = self::getSelectColumns();
		if(!$limit = \MySQLiTools::prepareLimitStatement($offset, $limit)){
			$limit = '';
		}

		$query = 'SELECT '.$this->getSelectColumns($metadata).'
		          FROM `'.static::TABLE.'`
		          '.$join.'
		          '.$where.'
		          '.$order.'
		          '.$limit;
		return $this->slaveQuery(new Query($query));
	}

	/**
	 * @see DAO_SignupList::getListCount()
	 */
	public function getListCount(Parser $metadata, \DatabaseListHelperFilter $filter){
		$filters = $this->_prepareFilterStatements($filter);
		$join = $filters['join'];
		$where = $filters['where'];

		$query = 'SELECT COUNT(*) AS `count`
		          FROM `'.static::TABLE.'`
		          '.$join.'
		          '.$where;
		$query = $this->slaveQuery(new Query($query));
		$query = $query->fetchArray();
		return $query['count'];
	}


	//*****************************************************************//
	//************************ Private methods ************************//
	//*****************************************************************//
	/**
	 * Create filter statement based on defined filters.
	 *
	 * @param DatabaseListHelperFilter $filter
	 * @return string filter statement
	 * @internal
	 */
	private function _prepareFilterStatements(\DatabaseListHelperFilter $filter){
		$filters['where'] = 'WHERE 1 ';
		$filters['join'] = '';

		if($filter->count() > 0){
			/* Filter statement */
			if($customer = $filter->get('fk_customers')){
				$filters['where'] .= ' AND fk_customers='.(int) $customer.' ';
			}

			/* Filter statement end */
		}
		return $filters;
	}

	public function getSelectColumns(Parser $metadata){
		foreach ($metadata->getMetadataProperties() as $property) {
			$name = $property->getName();
			if(!$datafield = $property->getValue('datafield')){
				$datafield = $name;
			}

			if($property->getValue('type') == 'timestamp'){
				$columns[] = ' UNIX_TIMESTAMP(`'.$datafield.'`) AS `'.$name.'`';
			} else {
				$columns[] = '`'.$datafield.'` AS `'.$name.'`';
			}
		}
		return implode(', ',$columns);
	}
/*
	private function _createWhereFromProperties(array $properties){
		$where = 'WHERE 1 ';
		foreach ($properties as $key => $property) {
			if(!$datafield = $property->getValue('datafield')){
				$datafield = $key;
			}
			$where .= ' AND `'.$datafield.'`=? ';
		}
		return $where;
	}
*/
}