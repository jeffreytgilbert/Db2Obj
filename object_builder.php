<?php

/*
 * @TODO There is a bug with handling composite key sets or UNIQUE(`id`,`name`,`pass`); << only gets id, ignores the rest due to comma exploding way early on.
 */

// Load App Structure
require_once( dirname(__FILE__).'/Db2Obj/Db2Obj.php' );
// Load Storage Engine Drivers
require_once( dirname(__FILE__).'/DataTypes/MySQL/MySQL2Obj.php' );
// Load Language Drivers
require_once( dirname(__FILE__).'/CodeFormats/PHP/Obj2PHP.php' );


$DbObj = new MySQL2Obj('example.sql');
$DbObj->parseToDataObj();
$PHPObj = new Obj2PHP( $DbObj );
$PHPObj->convert();

echo "Done writing files\n";

//write out files based on foreach of $db structure;