<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib database MySQLi engine.
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
namespace Corelib\Base\Database\MySQLi;
use Corelib\Base\Database\Engine as ConnectionEngine;
use Corelib\Base\Database\Exception, mysqli;

//*****************************************************************//
//************** MySQLi Database Engine constants *****************//
//*****************************************************************//
/**
 * MySQLi Descending sort order key.
 *
 * @var string
 */
if(!defined('DATABASE_ORDER_DESC')){
	define('DATABASE_ORDER_DESC', 'DESC');
}

/**
 * MySQLi Ascending sort order key.
 *
 * @var string
 */
if(!defined('DATABASE_ORDER_ASC')){
	define('DATABASE_ORDER_ASC', 'ASC');
}

/**
 * MySQLi Database engine.
 *
 * The Database class provides all basic functionality,
 * for communicating with a mysql database using PHP's
 * mysqli extension.
 *
 * @api
 */
class Engine implements ConnectionEngine {


	//*****************************************************************//
	//************ MySQLi Database Engine class properties ************//
	//*****************************************************************//
	/**
	 * @var mysqli database connection
	 * @internal
	 */
	private $connection = null;
	/**
	 * @var string hostname
	 * @internal
	 */
	private $hostname = null;
	/**
	 * @var string username
	 * @internal
	 */
	private $username = null;
	/**
	 * @var string password
	 * @internal
	 */
	private $password = null;
	/**
	 * @var string database
	 * @internal
	 */
	private $database = null;
	/**
	 * @var string connection encoding
	 * @internal
	 */
	private $charset = 'utf8';
	/**
	 * @var boolean reconnect if connection is lost
	 * @internal
	 */
	private $reconnect = false;


	//*****************************************************************//
	//************ MySQLi Database Engine class constants *************//
	//*****************************************************************//
	/**
	 * MySQLi engine DAO class prefix.
	 *
	 * @var string prefix
	 */
	const PREFIX = 'MySQLi';


	//*****************************************************************//
	//************* MySQLi Database Engine class methods **************//
	//*****************************************************************//
	/**
	 * Create new connection.
	 *
	 * @uses  Engine::$hostname
	 * @uses  Engine::$username
	 * @uses  Engine::$password
	 * @uses  Engine::$database
	 * @uses  Engine::$reconnect
	 * @uses  Engine::$charset
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param boolean $reconnect false if no reconnect should be done, else true
	 * @param string $charset connection encoding
	 */
	public function __construct($hostname, $username, $password, $database, $reconnect=false, $charset='utf8'){
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->reconnect = $reconnect;
		$this->charset = $charset;
	}

	/**
	 * Execute query.
	 *
	 * @throws BaseException
	 * @uses Engine::$connection
	 * @uses Engine::_connect()
	 * @uses Engine::$reconnect
	 * @uses Engine::setInstance()
	 * @uses Engine::execute()
	 * @uses Engine::getErrno()
	 * @param \Corelib\Base\Database\Query {@link Query mysqli query object}
	 * @return true on success, else return false
	 */
	public function query(\Corelib\Base\Database\Query $query){
		if(!$query instanceof Query){
			throw new Exception('Invalid Query Object, object must be instance of Corelib\Base\Database\MySQLi\Query');
		}
		if(is_null($this->connection)){
			$this->_connect();
		}
		if($this->reconnect){
			while(true){
				$query->execute();
				if($query->getErrno() >= 2000){
					sleep(1);
				} else {
					return true;
				}
			}
		} else {
			return $query->execute($this->connection);
		}
		return false;
	}

	/**
	 * Get dao class prefix.
	 *
	 * @see Engine::getPrefix()
	 * @return string
	 */
	public function getPrefix(){
		return self::PREFIX;
	}

	/**
	 * Start transaction.
	 *
	 * @uses Query
	 * @uses Engine::query()
	 * @see \Corelib\Base\Database\Engine::startTransaction()
	 */
	public function startTransaction(){
		$this->query(new Query('START TRANSACTION'));
	}
	/**
	 * Commit transaction.
	 *
	 * @uses Query
	 * @uses Engine::query()
	 * @see \Corelib\Base\Database\Engine::commit()
	 */
	public function commit(){
		$this->query(new Query('COMMIT'));
	}

	/**
	 * Rollback transaction.
	 *
	 * @uses Query
	 * @uses Engine::query()
	 * @see Engine::rollback()
	 */
	public function rollback(){
		$this->query(new Query('ROLLBACK'));
	}

	/**
	 * Analyse query.
	 *
	 * @see \Corelib\Base\Database\Engine::analyse()
	 * @uses Query::getQuery()
	 * @uses Engine::query()
	 * @param \Corelib\Base\Database\Query {@link Query mysqli query object}
	 * @return array
	 */
	public function analyse(\Corelib\Base\Database\Query $query){
		$query = new Query('EXPLAIN '.$query->getQuery());
		$this->query($query);


		if($fieldsObj = $query->fetchFields()){
			foreach ($fieldsObj as $field){
				$fields[] = $field->name;
			}

			$i = 0;
			while($out = $query->fetchArray()){
				foreach ($fields as $field){
					$rows[$i][] = $out[$field];
				}
				$i++;
			}
			return array('columns'=>$fields, 'rows'=>$rows);
		} else {
			return false;
		}
	}

	/**
	 * Escaping string.
	 *
	 * @param string $string
	 * @return string.
	 */
	public function escapeString($string){
		if(is_null($this->connection)){
			$this->_connect();
		}
		return $this->connection->real_escape_string($string);
	}

	/**
	 * Convert unix timestamp to database timestamp.
	 *
	 * @param integer $timestamp unix timestamp to convert
	 * @return string database compatible timestamp
	 */
	public function createTimestamp($timestamp){
		return strftime('%Y-%m-%d %X', $timestamp);
	}

	/**
	 * Connect to database.
	 *
	 * @uses Engine::$hostname
	 * @uses Engine::$username
	 * @uses Engine::$password
	 * @uses Engine::$database
	 * @uses Engine::$connection
	 * @uses Engine::$charset
	 * @return true on success, else return false
	 * @internal
	 */
	private function _connect(){
		$this->connection = @new mysqli($this->hostname, $this->username, $this->password, $this->database);
		if($this->connection->connect_errno === 0){
			$this->connection->query('SET NAMES '.$this->charset);
			$this->connection->query('SET CHARACTER SET '.$this->charset);
			return true;
		} else {
			throw new BaseException($this->connection->connect_error, E_USER_ERROR);
		}
	}
}
?>