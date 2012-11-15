<?php
namespace Corelib\Base\ObjectRelationalMapping\DataAccess;

use \Corelib\Base\ObjectRelationalMapping\Metadata\Parser;

abstract class Object extends \Corelib\Base\Database\DataAccessObject {

	const DATA_ACCESS_METADATA_AUTO_INCREMENT = 'auto_increment';

	abstract public function create(Parser $metadata, array $properties, \DatabaseDataHandler $data);
	abstract public function update(Parser $metadata, array $properties, array $kvalues, \DatabaseDataHandler $data);
	abstract public function delete(Parser $metadata, array $properties, array $kvalues);
	abstract public function getFromProperties(Parser $metadata, array $properties, array $values);

}
?>