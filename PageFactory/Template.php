<?php
namespace Corelib\Base\PageFactory;

abstract class Template {
	abstract public function prepare();
	abstract public function render();
	abstract public function addContent(Output $content);
	abstract public function addSettings(Output $settings);

}