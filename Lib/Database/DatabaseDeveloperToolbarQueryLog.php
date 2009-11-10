<?php
class DatabaseDeveloperToolbarQueryLog extends PageFactoryDeveloperToolbarItem {
	private $log = array();
	private $time = 0;
	private $content = '';

	public function getToolbarItem(){
		$this->log = Database::getInstance()->getQueryLog();
		$this->_prepareContent();
		return '<img src="corelib/resource/manager/images/page/icons/toolbar/database.png" alt="database" title="Database stats"/> '.(round($this->time, 4) * 1000).' ms.';
	}

	private function _prepareContent(){
		$duplicates = array();
		$duplicate_count = 0;
		$result = '';
		foreach ($this->log as $key => $line){
			$result .= '<div>';
			$this->time += $line['time'];
			if(!isset($duplicates[md5($line['query'])])){
				$duplicates[md5($line['query'])] = ($key + 1);
				$result .= '<h2 onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }">#'.($key + 1).' Query ('.round($line['time'], 4).'s) ';
			} else {
				$duplicate_count++;
				$result .= '<h2 class="warning" onclick="if(document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display == \'none\'){ document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'block\' } else { document.getElementById(\'DatabaseQueryLog'.$key.'\').style.display = \'none\' }"><u>#'.($key + 1).' Query ('.round($line['time'], 4).'s) <b>(#'.$duplicates[md5($line['query'])].')</b></u> ';
			}
			$result .= '<small>'.substr($line['query'], 0, 200).'</small></h2>';

			if($line['error']['code'] > 0){
				$result .= '<h3>Error Code: '.$line['error']['code'].'</h3>';
				$result .= '<p>'.$line['error']['message'].'</p>';
			}

			$result .= '<div id="DatabaseQueryLog'.$key.'" style="display: none;"><h3>SQL</h3><pre>'.trim($line['query']).'</pre><br/>';


			if(is_array($line['analysis'])){
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

	public function getContent(){
		return $this->content;
	}
}
?>