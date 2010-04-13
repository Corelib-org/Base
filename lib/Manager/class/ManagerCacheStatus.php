<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Base manager cache status output class.
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
 * @author Steffen Soerensen <ss@corelib.org>
 * @copyright Copyright (c) 2005-2008 Steffen Soerensen
 * @license http://www.gnu.org/copyleft/gpl.html
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @link http://www.corelib.org/
 * @version 1.0.0 ($Id: Interfaces.php 5218 2010-03-16 13:07:41Z wayland $)
 * @internal
 */


//*****************************************************************//
//****************** ManagerCacheStatus class *********************//
//*****************************************************************//
/**
 * Manager cache status output class.
 *
 * @category corelib
 * @package Base
 * @subpackage Manager
 *
 * @internal
 */
class ManagerCacheStatus implements Output {


	//*****************************************************************//
	//************* ManagerCacheStatus class properties ***************//
	//*****************************************************************//
	/**
	 * @var array list of cached files and sizes
	 * @internal
	 */
	private $cache = array();

	/**
	 * @var Converter cache size converter
	 * @internal
	 */
	private $cache_size_converter = null;


	//*****************************************************************//
	//*************** ManagerCacheStatus class methods ****************//
	//*****************************************************************//
	/**
	 * Create new instance.
	 *
	 * @return void
	 * @internal
	 */
	public function __construct(){
		$this->_findFiles(BASE_CACHE_DIRECTORY);
	}

	/**
	 * Set cache size converter.
	 *
	 * @param Converter $converter
	 * @return boolean true on success, else return false.
	 */
	public function setCacheSizeConverter(Converter $converter){
		$this->cache_size_converter = $converter;
		return true;
	}

	/**
	 * Clear cache.
	 *
	 * @return boolean true on success, else return false
	 */
	public function clear(){
		foreach ($this->cache as $file => $size){
			if(is_dir($file)){
				rmdir($file);
			} else {
				unlink($file);
			}
		}
		$this->cache = array();
		return true;
	}

	/**
	 * Get XML Content.
	 *
	 * @see Output::getXML()
	 * @return DOMElement
	 * @internal
	 */
	public function getXML(DOMDocument $xml){
		$status = $xml->createElement('manager-cache-status');
		if(is_null($this->cache_size_converter)){
			$status->setAttribute('size', array_sum($this->cache));
		} else {
			$status->setAttribute('size', $this->cache_size_converter->convert(array_sum($this->cache)));
		}
		return $status;
	}

	/**
	 * Search for cached files
	 *
	 * @param string $dir directory to search
	 * @return void
	 * @internal
	 */
	private function _findFiles($dir){
		if(substr($dir, 0, -1) != '/'){
			$dir = $dir.'/';
		}
		if(is_dir($dir) && is_readable($dir)){
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if($entry{0} != '.' && is_file($dir.$entry)){
					$this->cache[$dir.$entry] = filesize($dir.$entry);
				} else if ($entry{0} != '.' && is_dir($dir.$entry)){
					$this->_findFiles($dir.$entry);
				}
			}
			$this->cache[$dir.$entry] = 0;
		}
	}
}
?>