<?php
abstract class ManagerWidget implements Output {
	/**
	 * @var DOMNode
	 */
	protected $settings = null;
	
	private $template = null;
	
	public function __construct(){
		
	}
	
	final public function setTemplate(PageFactoryDOMXSLTemplate $template){
		if(is_null($this->template)){
			$this->template = $template;
		}
	}
	
	public function setSettings(DOMNode $settings){
		$this->settings = $settings;
	}
	
	protected function _getSettingsValue($nodeName, $attribute=null){
		$list = $this->settings->getElementsByTagName($nodeName);
		if($list->length > 0){
			if(!is_null($attribute)){
				$attribute = $list->item(0)->getAttribute($attribute);
				if(!empty($attribute) && !is_null($attribute)){
					return $attribute;
				} else {
					return false;
				}
			} else {
				if(!empty($list->item(0)->nodeValue)){
					return $list->item(0)->nodeValue;
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
	}
	
	public function addTemplate($filename){
		$this->template->addTemplate($filename);
	}
	public function addStyleSheet($filename){
		$this->template->addStyleSheet($filename);
	}
	public function addJavaScript($filename){
		$this->template->addJavaScript($filename);
	}

}

class ManagerWidgetErrorLog extends ManagerWidget {
	private $logfile = null;
	
	public function getXML(DOMDocument $xml){
		$this->addTemplate('Base/Share/Resources/XSLT/Pages/manager/errorlog.xsl');
		$errorlog = $xml->createElement('errorlog');
		
		$logentries = array();
		
		if($this->_openLogfile()){
			$entry = false;
			while ($line = fgets($this->logfile)) {
				if(!$entry){
					if(strstr($line, '--====MD5')){
						$entry = true;
						// --====MD5 22b1b3a5015c49299a2029a1bd592de8 Time: Mon, 21 Apr 2008 21:12:35 +0200
						$current = preg_match('/^--====MD5 ([a-f0-9]+)\sTime:\s(.*)$/', $line, $matches);
						$current = $matches[1];
						if(!isset($logentries[$current]['xml'])){
							$logentries[$current]['xml'] = $errorlog->appendChild($xml->createElement('entry'));
							$logentries[$current]['xml']->setAttribute('id', $current);
							$logentries[$current]['contentlines'] = $logentries[$current]['xml']->appendChild($xml->createElement('contentlines'));
							$logentries[$current]['dates'] = $logentries[$current]['xml']->appendChild($xml->createElement('dates'));
							$logentries[$current]['tracelines'] = $logentries[$current]['xml']->appendChild($xml->createElement('tracelines'));														
						}
						$logentries[$current]['dates']->appendChild($xml->createElement('date', $matches[2]));
					}
				} else {
					if(!strstr($line, 'EOF====--') && !isset($logentries[$current]['complete'])){
						$line = trim($line);
						if(strstr($line, 'Error File:')){
							$logentries[$current]['xml']->appendChild($xml->createElement('file', trim(str_replace('Error File: ', '', $line))));
						} else if(strstr($line, 'Remote Address:')){
						} else if(strstr($line, 'Error Code:')){
							$logentries[$current]['xml']->appendChild($xml->createElement('code', trim(str_replace('Error Code: ', '', $line))));
						} else if(strstr($line, 'Error Line:')){
							$logentries[$current]['xml']->appendChild($xml->createElement('line', trim(str_replace('Error Line: ', '', $line))));
						} else if(strstr($line, 'Request URI:')){
							$logentries[$current]['xml']->appendChild($xml->createElement('uri', trim(str_replace('Request URI: ', '', $line))));
						} else if(preg_match('/^#([0-9]+)\s(.*)$/', $line, $matches)){
							$logentries[$current]['tracelines']->appendChild($xml->createElement('traceline', $matches[2]));
						} else {
							if(!empty($line)){
								$logentries[$current]['contentlines']->appendChild($xml->createElement('contentline', trim($line)));	
							}
						}
						/*
					Error Code: Notice
Error File: /usr/home/webroot/corelib/Base/Lib/Handlers/ErrorHandler.php
Error Line: 61
Request URI: /corelib/manager/dashboard/?xml
Remote Address: 10.37.129.2

Undefined variable: test 
 /usr/home/webroot/corelib/Base/Lib/Manager/Pages/Get/Manager.php at line 12

#0 /usr/home/webroot/corelib/Base/Lib/Manager/Pages/Get/Manager.php(12): BaseError(8, 'Undefined varia...', '/usr/home/webro...', 12, Array)
#1 /usr/home/webroot/corelib/Base/Lib/PageFactory/PageFactory.php(82) : eval()'d code(1): WebPage->dashboard()
#2 /usr/home/webroot/corelib/Base/Lib/PageFactory/PageFactory.php(82): eval()
#3 /usr/home/webroot/corelib/Base/Lib/PageFactory/PageFactory.php(241): PageFactoryTemplateEngine->build(Object(WebPage), 'dashboard()')
#4 /usr/home/webroot/backoffice/share/doc/Dummy/index.php(14) : eval()'d code(1): PageFactory->build(Object(WebPage))
#5 /usr/home/webroot/backoffice/share/doc/Dummy/index.php(14): eval()
#6 {main}					
*/	
					} else {
						$logentries[$current]['complete'] = true;
						$entry = false;
					}
				}
			}
		}
		return $errorlog;
	}
	
	public function &getArray(){
		
	}
	
	private function _openLogFile(){
		if(is_file(BASE_ERROR_LOGFILE)){
			if(is_null($this->logfile)){
				$this->logfile = fopen(BASE_ERROR_LOGFILE, 'r');
			}
			return true;
		} else {
			return false;
		}
	}
}
?>