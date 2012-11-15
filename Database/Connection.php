<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database connection proxy.
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
 * @author Steffen Sørensen <steffen@sublife.dk>
 * @copyright Copyright (c) 2012 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version ($Id$)
 * @see Corelib\Base\Database\Connection
 */
namespace Corelib\Base\Database;

/**
 * Database Connection proxy class.
 *
 * The connection class replaces the old Database Singleton
 * class.
 *
 * @todo write documentation and usage example
 * @see Corelib\Base\ServiceLocator\Locator
 * @see Database
 * @api
 */
class Connection implements \Corelib\Base\ServiceLocator\Service {

	/**
	 * Master database engine instance.
	 *
	 * @var Corelib\Base\Database\Engine
	 * @internal
	 */
	private $master = null;

	/**
	 * Slave database engine instance.
	 *
	 * @var Corelib\Base\Database\Engine
	 * @internal
	 */
	private $slave = null;

	/**
	 * Array of database engines linked to a shard.
	 *
	 * @var array Corelib\Base\Database\Engine
	 * @internal
	 */
	private $shards = array();

	/**
	 * Create new database connection instance.
	 *
	 * @param Engine $master
	 * @param Engine $slave
	 */
	public function __construct(Engine $master, Engine $slave=null){
		$this->master = $master;
		$this->slave = $slave;
		if(is_null($this->slave)){
			$this->slave = $master;
		}
	}

	/**
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string
	 * @deprecated will be removed and reimplimented at a later time.
	 */
	public function escapeString($string){
		return $this->master->escapeString($string);
	}

	/**
	 * Get instance of specific DataAccessObject object.
	 *
	 * Returns a instance for a DataAccessObject, compatible with
	 * the current active database engine.
	 *
	 * @param string $class class name
	 * @param string $namespace
	 * @see Connection
	 * @see Engine
	 * @return DataAccessObject
	 * @api
	 */
	public function getDAO($class, $namespace=null){
		$prefix = $this->_getPrefix($class);
		if(!is_null($namespace)){
			if(!empty($namespace)){
				$namespace .= '\\';
			}
			$class = $namespace.'DataAccess\\'.$prefix.'\\'.$class;
		} else {
			$class = $prefix.'_'.$class;
		}

		// maintain backwards compatability for old dao classes
		if(is_callable($class.'::getInstance')){
			$dao = call_user_func($class.'::getInstance');
		} else {
			$dao = new $class($this);
		}
		return $dao;
	}

	/**
	 * Execute a query using the master connection.
	 *
	 * @param Query $query Query to execute
	 * @param string $shard
	 *
	 * @return Query Executed Query
	 *
	 * @uses Connection::_runQuery()
	 * @uses Connection::_getMasterConnection()
	 * @api
	 */
	public function masterQuery(Query $query, $shard=null){
		$this->_runQuery($this->_getMasterConnection($shard), $query, $shard);
		return $query;
	}

	/**
	 * Execute a query using the slave connection.
	 *
	 * WARNING: Do not send modifying queries using the connection.
	 *
	 * @uses Database::_runQuery()
	 * @uses Connection::_getSlaveConnection()
	 * @param Query $query Query to execute
	 * @param string Shard which shard
	 * @return Query Executed Query
	 */
	public function slaveQuery(Query $query, $shard=null){
		$this->_runQuery($this->_getSlaveConnection($shard), $query, $shard);
		return $query;
	}


	/**
	 * Execute query.
	 *
	 * @param Engine $instance
	 * @param Query $query
	 * @param string $shard
	 * @return bool
	 * @internal
	 */
	private function _runQuery(Engine $instance, Query $query, $shard=null){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			\Logger::info(get_class($instance).': '.$query->getQuery(), 1);
			$instance->query($query);
			/*if(DATABASE_SHOW_QUERY_LOG){
				$start = microtime(true);

				$e = new \Exception();

				$this->query_log[] = array('query' => $query->getQuery(),
				                           'error' => array('code' => $query->getErrno(),
				                                            'message' => $query->getError()),
				                           'time' => round(microtime(true) - $start, 5),
				                           'backtrace' => $e->getTraceAsString(),
				                           'analysis' => $instance->analyse($query),
				                           'shard' => $shard,
				                           'engine' => get_class($instance));
			}
			*/
		} else {
			$instance->query($query);
		}
		return $this->_error($query);
	}

	/**
	 * Get DAO Class master server.
	 *
	 * @param string class name
	 * @return string prefix
	 */
	private function _getMasterConnection($shard=null){
		if(is_null($shard) || !isset($this->shards[$shard])){
			return $this->master;
		} else {
			return $this->shards[$shard]['master'];
		}
	}

	/**
	 * Get DAO Class prefix.
	 *
	 * @param string class name
	 * @return string prefix
	 */
	private function _getSlaveConnection($shard=null){
		if(isset($this->shards[$shard]['slave'])){
			return $this->shards[$shard]['slave'];
		} else if(is_null($shard) || !isset($this->shards[$shard]['master'])){
			return $this->slave;
		} else {
			return $this->shards[$shard]['master'];
		}
	}

	/**
	 * Get DAO Class prefix.
	 *
	 * @param string class name
	 * @return string prefix
	 */
	private function _getPrefix($class){
		if(!isset($this->shards[$class])){
			return $this->master->getPrefix();
		} else {
			return $this->shards[$class]['prefix'];
		}
	}

	/**
	 * Handle database errors.
	 *
	 * @uses BaseException
	 * @uses Query::getError()
	 * @uses Query::getErrno()
	 * @param Query $query
	 * @throws Exception
	 * @return boolean true if no error have occured else return false and throw a exception
	 * @internal
	 */
	private function _error(Query $query){
		if($query->getErrno()){
			throw new Exception($query->getError(), $query->getErrno());
		}
		return true;
	}



}