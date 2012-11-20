<?php
namespace Corelib\Base\PageFactory;

abstract class Template {
	abstract public function prepare();
	abstract public function render();
	abstract public function addContent($content);
	abstract public function addSettings($settings);

}