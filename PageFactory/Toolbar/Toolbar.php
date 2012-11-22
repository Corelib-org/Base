<?php
namespace Corelib\Base\PageFactory\Toolbar;

use Corelib\Base\Tools\UUID,
	Corelib\Base\ServiceLocator\Service,
	Corelib\Base\ServiceLocator\Autoloadable;

/**
 * Page Factory Developer toolbar.
 *
 * @category corelib
 * @package Base
 * @subpackage PageFactory
 */
final class Toolbar implements Service,Autoloadable {


	//*****************************************************************//
	//********* PageFactoryDeveloperToolbar Class properties **********//
	//*****************************************************************//
	/**
	 * Singleton Object Reference.
	 *
	 * @var PageFactoryDeveloperToolbar
	 * @internal
	 */
	private static $instance = null;

	/**
	 * Toolbar items.
	 *
	 * @var array toolbar items
	 */
	private $items = array();

	public function __construct(){
		if(php_sapi_name() == 'cli' && defined('BOOTSTRAP_DEVELOPER_TOOLBAR')){
			define('PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR', false);
		}
	}



	/**
	 * Add toolbar item to toolbar.
	 *
	 * @param PageFactoryDeveloperToolbarItem $item
	 * @return PageFactoryDeveloperToolbarItem
	 */
	public function addItem(Item $item){
		$this->items[] = $item;
		return $item;
	}

	public function inject(&$data){
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL && (!defined('PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR') || PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR == true)){
			$data = str_replace('</body', $this->draw().'</body', $data);
		}
	}

	/**
	 * Create toolbar.
	 *
	 * Generate toolbar when object is echoed.
	 *
	 * @return string toolbar.
	 * @internal
	 */
	public function draw(){
		$headers = headers_list();
		$show_bar = false;
		foreach($headers as $header){
			if(stristr($header, 'Content-Type') && stristr($header, 'html')){
				$show_bar = true;
				break;
			}
		}

		if($show_bar){
			$data = '';
			$toolbar = '';
			foreach($this->items as $item){
				$id = 'developer-toolbar-'.UUID::generate();
				$tool = $item->getToolbarItem();
				if($content = $item->getContent()){
					// $toolbar .= '<a onclick="if(document.getElementById(\''.$id.'\').style.display == \'none\'){ document.getElementById(\''.$id.'\').style.display = \'block\' } else { document.getElementById(\''.$id.'\').style.display = \'none\' }">'.$tool.'</a	> &nbsp; ';
					$toolbar .= '<a onclick="developerToolbarToggle(\''.$id.'\');">'.$tool.'</a	> &nbsp; ';
					$data .= '<div class="developer-toolbar-item" id="'.$id.'" style="display: none;">'.$content.'</div>';
				} else {
					$toolbar .= $tool.'  &nbsp;  ';
				}
			}

			$output  = '<link rel="stylesheet" type="text/css" href="corelib/resource/manager/css/toolbar.css" />';
			$output .= '<script type="text/javascript">var developer_toolbar_open = 0; function developerToolbarToggle(id){ if(document.getElementById(id).style.display == \'none\'){ document.getElementById(id).style.display = \'block\'; developer_toolbar_open++ } else { document.getElementById(id).style.display = \'none\'; developer_toolbar_open-- } if(developer_toolbar_open > 0){ document.getElementById(\'developer-toolbar\').style.position =\'absolute\'; document.getElementById(\'developer-toolbar\').style.opacity =\'1\'; } else { document.getElementById(\'developer-toolbar\').style.position =\'fixed\'; document.getElementById(\'developer-toolbar\').style.opacity = \'\'; } }</script>';
			$output .= '<div id="developer-toolbar">';
			// $output .= '<div style="float: right;"><a href="javascript:void(0)" onclick="document.getElementById(\'developer-toolbar\').style.display=\'none\';"><img src="corelib/resource/manager/images/icons/toolbar/close.png" alt="Close toolbar" title="Close toolbar" style="border: 0px;"/></a>&#160;</div>';
			$output .= '<div class="toolbar">'.$toolbar.'</div><div class="data">'.$data.'</div></div>';
			return $output;
		}
	}

	/**
	 * Convert object to string.
	 *
	 * converting the developer toolbar to a string results in the html code
	 * returned by {@link PageFactoryDeveloperToolbar::draw()} this method is
	 * deprecated and is only here for backwards compatibility.
	 *
	 * @uses PageFactoryDeveloperToolbar::draw()
	 */
	/*
	public function __toString(){
		trigger_error('PageFactoryDeveloperToolbar::__toString() is deprecated', E_USER_DEPRECATED);
		if(BASE_RUNLEVEL >= BASE_RUNLEVEL_DEVEL && (!defined('PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR') || PAGE_FACTORY_SHOW_DEVELOPER_TOOLBAR == true)){
			return $this->draw();
		} else {
			return '';
		}
	}
	*/
}
?>