<?php
/* vim: set tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Corelib Database Query log toolbar item.
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
 * @version 1.0.0 ($Id: MySQLi.php 5097 2009-11-12 08:51:33Z wayland $)
 */

//*****************************************************************//
//************ DatabaseDeveloperToolbarQueryLog class *************//
//*****************************************************************//
/**
 * Database query log toolbar item.
 *
 * @category corelib
 * @package Base
 * @subpackage Database
 */
class DatabaseDeveloperToolbarQueryLog extends PageFactoryDeveloperToolbarItem {


	//*****************************************************************//
	//****** DatabaseDeveloperToolbarQueryLog class properties ********//
	//*****************************************************************//
	/**
	 * @var array log
	 * @internal
	 */
	private $log = array();

	/**
	 * Total time spent.
	 *
	 * @var float
	 * @internal
	 */
	private $time = 0;

	/**
	 * @var string
	 * @internal
	 */
	private $content = '';


	//*****************************************************************//
	//******* DatabaseDeveloperToolbarQueryLog class methods **********//
	//*****************************************************************//
	/**
	 * Get toolbar item.
	 *
	 * @see PageFactoryDeveloperToolbarItem::getToolbarItem()
	 * @return string
	 * @internal
	 */
	public function getToolbarItem(){
		$this->log = Database::getInstance()->getQueryLog();
		$this->_prepareContent();
		return '<img src="corelib/resource/manager/images/icons/toolbar/database.png" alt="database" title="Database stats"/> '.(round($this->time, 4) * 1000).' ms.';
	}

	/**
	 * Prepare toolbar content.
	 *
	 * @return void
	 * @internal
	 */
	private function _prepareContent(){
		$duplicates = array();
		$duplicate_count = 0;
		$result = '';
		foreach ($this->log as $key => $line){
			$result .= '<div>';
			$this->time += $line['time'];
			if(!isset($duplicates[md5($line['query'])])){
				$duplicates[md5($line['query'])] = ($key + 1);
				$result .= '<h2 onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }">#'.($key + 1).' Query ('.(round($line['time'], 4) * 1000).'ms) ';
			} else {
				$duplicate_count++;
				$result .= '<h2 class="warning" onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }"><u>#'.($key + 1).' Query ('.(round($line['time'], 4) * 1000).'ms) <b>(#'.$duplicates[md5($line['query'])].')</b></u> ';
			}
			$result .= '<small>'.substr($line['query'], 0, 200).'</small></h2>';

			if($line['error']['code'] > 0){
				$result .= '<h3>Error Code: '.$line['error']['code'].'</h3>';
				$result .= '<p>'.$line['error']['message'].'</p>';
			}

			$result .= '<div id="DatabaseQueryLog'.$key.'" style="display: none;"><h3>SQL</h3><pre>'.trim($line['query']).'</pre><br/>';


			if(is_array($line['analysis']) && sizeof($line['analysis']) > 0){
				$result .= '<h3>Analysis</h3><table style="width: 100%; border-spacing: 0px;"><thead><tr>';
				foreach ($line['analysis']['columns'] as $column){
					$result .= '<th style="border: 1px solid; border-width: 0px 0px 1px 0px">'.$column.'</th>';
				}
				$result .= '<tr></thead>';
				foreach ($line['analysis']['rows'] as $rows){
					$result .= '<tr>';
					foreach ($rows as $columns){

						$result .= '<td style="border: 1px solid; border-width: 0px 0px 1px 0px">'.$columns.'</td>';
					}
					$result .= '</tr>';
				}


				$result .= '</table><br/>';
			}
			$result .= '<h3>Backtrace</h3><pre>'.$line['backtrace'].'</pre><br/>';
			$result .= '<hr/><br/></div></div>';
		}
		$view = '<h1>Query Log (Queries: '.sizeof($this->log).', Duplicates: '.$duplicate_count.', Time: '.$this->time.'s )</h1>';
		$view .= $result;
		$this->content = $view;
	}

	/**
	 * Get toolbar item content.
	 *
	 * @see PageFactoryDeveloperToolbarItem::etContent()
	 * @return string
	 * @internal
	 */
	public function getContent(){
		return $this->content;
	}
}
?>