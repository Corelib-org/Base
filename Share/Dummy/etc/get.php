<?php
// Generic Pages
$pages['/'] = 'lib/http/get/index.php';

/*
EXAMPLES ON USING CUSTOM METHOD OVERRIDES
$pages['/'] = 'lib/http/get/corelib/about.php';

// Static
$pages['/manager/ajax/page/create/'] = array('page'=>'lib/ajax/get/manager/page.php',
                                             'exec'=>'create');

// Dynamix regex                                             
$rpages[] = array('type'=>'regex',
                  'expr'=>'/^\/manager\/(test)\/([0-9]+)\/$/',
                  'exec'=>'\\1(\\2)',
                  'page'=>'lib/http/get/test.php');
                
// Dynamic Meta format  
$rpages[] = array('type'=>'PageFactoryMetaPageResolver',
                  'expr'=>'/manager/(function)/(int:id)/(string:somestring)/',
                  'exec'=>'id, somestring',
                  'page'=>'lib/http/get/test.php');
                  
// Dynamic Meta format with a static function
$rpages[] = array('type'=>'PageFactoryMetaPageResolver',
                  'expr'=>'/manager/(int:id)/(string:somestring)/',
                  'exec'=>'function: id, somestring',
                  'page'=>'lib/http/get/test.php');
*/
?>	