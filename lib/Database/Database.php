<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database abstraction layer.
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
 * @subpackage Database
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @copyright Copyright (c) 2009 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 * @link http://www.corelib.org/
 * @version 4.0.0 ($Id$)
 */


//*****************************************************************//
//****************** Basic Configuration Check ********************//
//*****************************************************************//
if(!defined('DATABASE_SHOW_QUERY_LOG')){
	/**
	 * Enable and disable query log.
	 *
	 * if this is set to false, no logging will be performed,
	 * if set to true a query log will apear at the end of
	 * each page.
	 *
	 * Default: false
	 */
	define('DATABASE_SHOW_QUERY_LOG', false);
}


//*****************************************************************//
//************************ Database Class *************************//
//*****************************************************************//
/**
 * Database class.
 *
 * The Database class provides all basic functionality,
 * for communicating with varios databases, as well as
 * looking up the proper DAO class
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class Database implements Singleton {


	//*****************************************************************//
	//******************* Database Class properties *******************//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var Database
	 */
	private static $instance = null;

	/**
	 * @var string DAO Object prefix
	 */
	private static $dao_prefix = null;

	/**
	 * @var DatabaseEngine slave connection
	 */
	private $slave = null;

	/**
	 * @var DatabaseEngine master connection
	 */
	private $master = null;

	/**
	 * Query log.
	 *
	 * Array containing a liste of executed queries
	 *
	 * @var array
	 */
	private $query_log = array();


	//*****************************************************************//
	//********************* Database Class methods ********************//
	//*****************************************************************//
	/**
	 * @ignore
	 */
	private function __construct(){ }

	/**
	 * Return instance of Database.
	 *
	 * Please refer to the {@link Singleton} interface for complete
	 * description.
	 *
	 * @uses Database
	 * @return Database
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Database();
		}
		return self::$instance;
	}

	/**
	 * Get instance of specified DAO object.
	 *
	 * @uses Database::$dao_prefix
	 * @return DatabaseDAO
	 */
	public static function getDAO($class){
		$eval = 'return '.self::$dao_prefix.'_'.$class.'::getInstance();';
		return eval($eval);
	}

	/**
	 * Connect to the master database.
	 *
	 * @uses Database::$dao_prefix
	 * @uses Database::$master
	 * @uses DatabaseEngine::getPrefix()
	 * @uses Database::$slave
	 * @param DatabaseEngine Database connection i use
	 */
	public function masterConnect(DatabaseEngine $master){
		$this->master = $master;
		self::$dao_prefix = $this->master->getPrefix();
		if(is_null($this->slave)){
			$this->slave = $master;
		}
	}

	/**
	 * Connect to a slave database.
	 *
	 * @uses Database::$slave
	 * @param DatabaseEngine Database connection i use
	 */
	public function slaveConnect(DatabaseEngine $slave){
		$this->slave = $slave;
	}

	/**
	 * Execute a query.
	 *
	 * When function is called it will detect if the statemant
	 * is a selecting or modifying statement and the execute
	 * the statement using the right connection, the master connection
	 * for modifications and the slave connection for selection.
	 *
	 * @uses Database::masterQuery()
	 * @uses Database::slaveQuery()
	 * @uses Database::_error()
	 * @uses Query::getQuery()
	 * @param Query $query Query to execute
	 * @return Query Executed Query
	 */
	public function query(Query $query){
		if(preg_match('/INSERT|SELECT INTO|UPDATE|MERGE|DELETE|TRUNCATE/', $query->getQuery())){
			$this->masterQuery($query);
		} else {
			$this->slaveQuery($query);
		}
		$this->_error($query);
		return $query;
	}

	/**
	 * Execute a query using the master connection.
	 *
	 * @uses Database::$query
	 * @uses Database::_runQuery()
	 * @param Query $query Query to execute
	 * return Query Executed Query
	 */
	public function masterQuery(Query $query){
		$this->_runQuery($this->master, $query);
		return $query;
	}

	/**
	 * Execute a query using the slave connection.
	 *
	 * WARNING: Do not send modifying queries using the connection.
	 *
	 * @uses Database::$query
	 * @uses Database::_runQuery()
	 * @param Query $query Query to execute
	 * @return Query Executed Query
	 */
	public function slaveQuery(Query $query){
		$this->_runQuery($this->slave, $query);
		return $query;
	}

	/**
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string.
	 */
	public function escapeString($string){
		return $this->master->escapeString($string);
	}

	/**
	 * Run query on a specific data connection.
	 *
	 * if a error occurs while running the
	 * query {@link Database::_error()} is called
	 *
	 * @uses DATABASE_SHOW_QUERY_LOG
	 * @uses BASE_RUNLEVEL
	 * @uses BASE_RUNLEVEL_DEVEL
	 * @uses Query::getErrno()
	 * @uses Query::getError()
	 * @uses Database::_error()
	 * @uses Exception::__toString()
	 * @uses DatabaseEngine::analyse()
	 * @uses DatabaseEngine::query()
	 * @param DatabaseEngine $instance
	 * @param Query $query
	 * @return true on success, else false
	 * @internal
	 */
	private function _runQuery(DatabaseEngine $instance, Query $query){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
			$start = microtime(true);
			$instance->query($query);
			$e = new Exception();
			$this->query_log[] = array('query' => $query->getQuery(),
			                           'error' => array('code' => $query->getErrno(),
			                                            'message' => $query->getError()),
			                           'time' => round(microtime(true) - $start, 5),
			                           'backtrace' => $e->getTraceAsString(),
			                           'analysis' => $instance->analyse($query));

		} else {
			$instance->query($query);
		}
		return $this->_error($query);
	}

	/**
	 * Handle database errors.
	 *
	 * @uses BaseException
	 * @uses Query::getError()
	 * @uses Query::getErrno()
	 * @param Query $query
	 * @throws BaseException
	 * @return boolean true if no error have occured else return false and throw a exception
	 * @internal
	 */
	private function _error(Query $query){
		try {
			if($query->getErrno()){
				throw new BaseException($query->getError(), $query->getErrno());
			}
		} catch (BaseException $e){
			echo $e;
			return false;
		}
		return true;
	}

	/**
	 * Get DAO class prefix.
	 *
	 * @uses Database::$dao_prefix
	 * @return string DAO class prefix
	 */
	public static function getPrefix(){
		return self::$dao_prefix;
	}

	/**
	 * Start database transaction.
	 *
	 * @uses Database::$master
	 * @uses DatabaseEngine::startTransaction()
	 */
	public function startTransaction(){
		$this->master->startTransaction();
	}

	/**
	 * Commit database transaction.
	 *
	 * @uses Database::$master
	 * @uses DatabaseEngine::commit()
	 */
	public function commit(){
		$this->master->commit();
	}

	/**
	 * Rollback database transaction.
	 *
	 * @uses Database::$master
	 * @uses DatabaseEngine::rollback()
	 */
	public function rollback(){
		$this->master->rollback();
	}

	/**
	 * Get query log.
	 *
	 * @uses Database::$query_log
	 * @return array Content of {@link Database::$query_log}
	 */
	public function getQueryLog(){
		return $this->query_log;
	}
}


//*****************************************************************//
//********************** Database interfaces **********************//
//*****************************************************************//
/**
 * Database Engine interface.
 *
 * This defines how a DatabaseEngine is implemented
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
interface DatabaseEngine {


	//*****************************************************************//
	//****************** Database interface methods *******************//
	//*****************************************************************//
	/**
	 * Execute query.
	 *
	 * @param Query $query
	 */
	public function query(Query $query);
	/**
	 * Get DAO class prefix.
	 *
	 * @return string
	 */
	public function getPrefix();
	/**
	 * Start transaction.
	 */
	public function startTransaction();
	/**
	 * Commit transaction.
	 */
	public function commit();
	/**
	 * Rollback transaction.
	 */
	public function rollback();
	/**
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string.
	 */
	public function escapeString($string);
	/**
	 * Analyse Query.
	 *
	 * Analyses a query and return a array with the results
	 * The return array is a multi dimensional array
	 *
	 * @todo add output array example
	 * @return array
	 */
	public function analyse(Query $query);
}


//*****************************************************************//
//********************* Database Query class **********************//
//*****************************************************************//
/**
 * Database Query abstract class.
 *
 * This defines how a Query is implemented
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
abstract class Query {


	//*****************************************************************//
	//*************** Database Query class properties *****************//
	//*****************************************************************//
	/**
	 * @var Query
	 */
	protected $query = null;


	//*****************************************************************//
	//***************** Database Query class methods ******************//
	//*****************************************************************//
	/**
	 * Create new query object instance.
	 *
	 * @param string $query
	 */
	public function __construct($query) {
		$this->query = $query;
	}
	/**
	 * Execute query.
	 *
	 * @return true on success, else return false
	 */
	abstract public function execute();
	/**
	 * Get query.
	 *
	 * @return string query
	 */
	abstract public function getQuery();
	/**
	 * Get error description.
	 *
	 * @return string error description
	 */
	abstract public function getError();
	/**
	 * Get error code.
	 *
	 * @return integer error code, else return false or 0
	 */
	abstract public function getErrno();
	/**
	 * Set database instance.
	 *
	 * Set database engine by passing the database connection resource
	 *
	 * @param mixed $instance
	 */
	abstract public function setInstance($instance);
	/**
	 * Get number of rows of select query.
	 *
	 * @return integer
	 */
	abstract public function getNumRows();
	/**
	 * Get last insert ID.
	 *
	 * @return integer row id, else return false
	 */
	abstract public function getInsertID();
	/**
	 * Fetch row as array from result.
	 *
	 * @return array row
	 */
	abstract public function fetchArray();
	/**
	 * Fetch fields from result.
	 *
	 * @return array fields
	 */
	abstract public function fetchFields();
	/**
	 * Get affected rows.
	 *
	 * return integer affected rows
	 */
	abstract public function getAffectedRows();
	/**
	 * Adjusts the result pointer to an arbitary row in the result.
	 *
	 * @param integer $offset The field offset. Must be between zero and the total number of rows minus one
	 * @return boolean true on success, else return false
	 */
	abstract public function dataSeek($offset);
}


//*****************************************************************//
//************************ DatabaseDAO class **********************//
//*****************************************************************//
/**
 * Database DAO abstract class.
 *
 * This defines how a DAO class is implemented
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
abstract class DatabaseDAO {


	//*****************************************************************//
	//**************** DatabaseDAO class properties *******************//
	//*****************************************************************//
	/**
	 * @var DatabaseEngine
	 */
	private $database;


	//*****************************************************************//
	//****************** DatabaseDAO class methods ********************//
	//*****************************************************************//
	/**
	 * @ignore
	 */
	protected function __construct(){
		$this->database = Database::getInstance();
	}

	/**
	 * Execute a query.
	 *
	 * @see Database::query()
	 * @uses DatabaseDAO::$database
	 */
	final protected function query(Query $query){
		return $this->database->query($query);
	}

	/**
	 * Execute a query using the master connection.
	 *
	 * @see Database::masterQuery()
	 * @uses DatabaseDAO::$database
	 */
	final protected function masterQuery(Query $query){
		return $this->database->masterQuery($query);
	}

	/**
	 * Execute a query using the slave connection.
	 *
	 * @see Database::slaveQuery();
	 * @uses DatabaseDAO::$database
	 */
	final protected function slaveQuery(Query $query){
		return $this->database->slaveQuery($query);
	}

	/**
	 * Escaping string.
	 *
	 * Escape string before using it in a query.
	 *
	 * @param string $string
	 * @return string.
	 */
	final protected function escapeString($string){
		return $this->database->escapeString($string);
	}

	/**
	 * Start transaction.
	 *
	 * @uses DatabaseDAO::$database
	 * @see Database::startTransaction()
	 */
	public function startTransaction(){
		return $this->database->startTransaction();
	}

	/**
	 * Commit transaction.
	 *
	 * @uses DatabaseDAO::$database
	 * @see Database::commit()
	 */
	public function commit(){
		return $this->database->commit();
	}

	/**
	 * Rollback transaction.
	 *
	 * @uses DatabaseDAO::$database
	 * @see Database::rollback()
	 */
	public function rollback(){
		return $this->database->rollback();
	}

	/**
	 * Put object to sleep.
	 *
	 * If object is put to sleep this method returns a empty array
	 * and by that resetting any information about old database connection
	 * instances.
	 *
	 * @return array
	 * @internal
	 */
	public function __sleep(){
		return array();
	}
	/**
	 * Wake up object.
	 *
	 * When the object wake up the constructor should be run again
	 * in order to determine the new database connection instance.
	 *
	 * @ignore
	 */
	public function __wakeup(){
		$this->__construct();
	}
}

if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL){
	PageFactoryDeveloperToolbar::getInstance()->addItem(new DatabaseDeveloperToolbarQueryLog());
}
?>