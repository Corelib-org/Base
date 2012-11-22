<?php
/**
 * Database DAO abstract class.
 *
 * This defines how a DAO class is implemented
 *
 * @category Corelib
 * @package Base
 * @subpackage Database
 * @deprecated
 */
abstract class DatabaseDAO extends \Corelib\Base\Database\DataAccessObject {

	public $database = null;

	public function __construct(){
		parent::__construct(Database::getInstance());
		$this->database = Database::getInstance();
	}

}

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
 * @deprecated
 */
class Database implements Singleton {


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
	 * @return \Corelib\Base\Database\Connection
	 */
	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\Database\Connection');
	}

	/**
	 * Get instance of specified DAO object.
	 *
	 * @uses Database::$dao_prefix
	 * @return DatabaseDAO
	 */
	public static function getDAO($class){
		return \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\Database\Connection')->getDAO($class);
	}
}


/**
 * MySQLi Query.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 * @deprecated
 */
class MySQLiQuery extends \Corelib\Base\Database\MySQLi\Query { }


/**
 * MySQLi Query Statement.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 * @deprecated
 */
class MySQLiQueryStatement extends \Corelib\Base\Database\MySQLi\Statement { }


/**
 * Page factory post engine class.
 *
 * This template is supposed to be used to handle post requests.
 * and therefore it is a no output template engine.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @deprecated
 */
class PageFactoryPostTemplate extends \Corelib\Base\PageFactory\Templates\POST { }

/**
 * DOMXSL Page factory template.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 * @deprecated
 */
class PageFactoryDOMXSLTemplate extends \Corelib\Base\PageFactory\Templates\XSLT { }


/**
 * Page factory page base.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @deprecated
 */
abstract class PageBase extends \Corelib\Base\PageFactory\Page { }


/**
 * Page factory.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @deprecated
 */
class PageFactory {
	public static function bootstrap($return=false){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			\Corelib\Base\PageFactory\Bootstrap::run(new \Corelib\Base\Routing\PHP('../zhosting/etc/post.php'));
		} else {
			\Corelib\Base\PageFactory\Bootstrap::run(new \Corelib\Base\Routing\PHP('../zhosting/etc/get.php'));
		}
		return true;
	}
}


/**
 * Cachable output event interface.
 *
 * impliment this event on all your object on delete and on update
 * events in order to make PageFactory clear the cache for the object
 * when it is being updated.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @deprecated
 */
interface CacheUpdateEvent { }

/**
 * @deprecated
 */
class XMLOutput extends \Corelib\Base\PageFactory\XMLOutput { }

/**
 * @deprecated
 */
class XMLTools extends \Corelib\Base\Tools\XML { }

/**
 * @deprecated
 */
abstract class CompositeOutput extends \Corelib\Base\PageFactory\CompositeOutput { }


/**
 * Cachable output interface.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 *
 * @author Steffen Sørensen <ss@corelib.org>
 * @since Version 5.0
 * @deprecated
 */
interface CacheableOutput { }

/**
 * @deprecated
 */
class DateConverter extends \Corelib\Base\Converters\Date\Strftime { }

/**
 * @deprecated
 */
class InputHandler {
	private function __construct() { }
	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\Input\Handler');
	}
}

class EventHandler {
	private function __construct() { }
	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\Event\Handler');
	}
}

/**
 * @deprecated
 */
class i18n {
	private function __construct() { }
	public static function getInstance(){
		return \Corelib\Base\ServiceLocator\Locator::get('Corelib\Base\i18n\Localize');
	}
}

/**
 * @deprecated
 */
class InputValidatorRegex extends \Corelib\Base\Input\Validators\Regex { }

/**
 * @deprecated
 */
abstract class EventAction extends \Corelib\Base\Event\Action { }

/**
 * Singleton interface
 *
 * Use this interface for defining the base of all Singleton
 * classes, however since PHP does not allow us to to include
 * the specifics of the constructer and __clone, you have to
 * remember to set the constructor and __clone private to get
 * the desired effect of a singleton class.
 *
 * @see http://en.wikipedia.org/wiki/Singleton_pattern
 * @category corelib
 * @package Base
 * @deprecated
 */
interface Singleton {


	//*****************************************************************//
	//***************** Singleton interface methods *******************//
	//*****************************************************************//
	/**
	 * @return Object Unique version of the object instance
	 */
	public static function getInstance();
}
