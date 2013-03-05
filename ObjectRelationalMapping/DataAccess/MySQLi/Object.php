<?php
namespace Corelib\Base\ObjectRelationalMapping\DataAccess\MySQLi;

use \Corelib\Base\ObjectRelationalMapping\Exception;
use \Corelib\Base\ObjectRelationalMapping\Metadata\Parser;
use Corelib\Base\Database\MySQLi\Statement;


abstract class Object extends \Corelib\Base\ObjectRelationalMapping\DataAccess\Object {

	public function __construct(\Corelib\Base\Database\Connection $database){
		parent::__construct($database);
		if(!defined('static::TABLE')){
			throw new Exception('Unable to instanciate class without const TABLE defined');
		}
	}

	public function create(Parser $metadata, array $keys, \DatabaseDataHandler $data){
		/* Special create fields */
		if($metadata->hasProperty('create_timestamp')){
			if($metadata->getProperty('create_timestamp')->getValue('type') == 'timestamp'){
				$data->setSpecialValue('create_timestamp', 'NOW()');
			}
		}
		/* Special create fields end */

		$columns = $data->getUpdatedColumns();
		$values = $data->getUpdatedColumnValues();

		$query = \MySQLiTools::makeInsertStatement(static::TABLE, $columns);
		$query = $this->masterQuery(new Statement($query, $values));

		if($query->getAffectedRows() > 0){
			$updated = call_user_func_array(array($data, 'getUpdatedColumns'), array_keys($keys));
			$kvalues = array();
			foreach($updated as $column){
				$kvalues[$column] = $data->get($column);
			}

			$auto_increment = $metadata->searchMetadataProperties(self::DATA_ACCESS_METADATA_AUTO_INCREMENT, true);
			if(sizeof($auto_increment) > 1){
				throw new Exception('More then one auto_increment property found, object can only have one.');
			} else if(sizeof($auto_increment) == 1 && ($id = (int) $query->getInsertID())) {
				$auto_increment = array_shift($auto_increment);
				$kvalues[$auto_increment->getName()] = $id;
			}
			return $this->getFromProperties($metadata, $keys, $kvalues);
		} else {
			return false;
		}
	}

	public function update(Parser $metadata, array $keys, array $kvalues, \DatabaseDataHandler $data){

		/* Special update fields */
		/* Special update fields end */

		$columns = $data->getUpdatedColumns();
		$values = $data->getUpdatedColumnValues();

		$query = \MySQLiTools::makeUpdateStatement(static::TABLE, $columns, $this->_createWhereFromProperties($keys).' LIMIT 1');
		$query = $this->masterQuery(new Statement($query, $values, $kvalues));

		/* After edit actions */
		/* After edit actions end */

		$updated = call_user_func_array(array($data, 'getUpdatedColumns'), array_keys($properties));
		foreach($updated as $column){
			$kvalues[$column] = $data->get($column);
		}
		if($query->getAffectedRows() > 0 && isset($kvalues)){
			return $this->getFromProperties($metadata, $keys, $kvalues);
		} else {
			return false;
		}
	}

	public function delete(Parser $metadata, array $properties, array $kvalues){
		$query = 'DELETE FROM `'.static::TABLE.'` '.$this->_createWhereFromProperties($properties).' LIMIT 1';
		$query = $this->masterQuery(new Statement($query, $kvalues));
	}

	public function getFromProperties(Parser $metadata, array $properties, array $values){
		$query = 'SELECT '.$this->getSelectColumns($metadata).' FROM `'.static::TABLE.'`
		         '.$this->_createWhereFromProperties($properties).' LIMIT 1';
		$query = $this->slaveQuery(new Statement($query, $values));
		return $query->fetchAssoc();
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
}
?>