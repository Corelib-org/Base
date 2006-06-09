<?php
include('Base.php');
echo 'Running IO Test for class Base....'."\n";
echo 'Testing if, Base::getInstance() can create new instances...';

class test extends Base {
	const INSTANCE = __CLASS__;
        function __construct(){ 
                parent::construct(__CLASS__);
        }
}

if(test::getInstance() instanceof test){
	echo 'OK'."\n";	
} else {
	echo 'FAILED'."\n";
}
?>
