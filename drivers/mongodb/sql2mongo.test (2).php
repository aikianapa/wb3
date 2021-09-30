<?php

include_once('mongo.php');

//$sql = 'SELECT * FROM test WHERE active = "on" OR role = "user" AND ( avatar > "" AND first_name > "");';
$sql = 'SELECT * FROM feedbackcloud.test;';
//$sql = 'SELECT * FROM feedbackcloud.test where tkd=15 and id<13;';

$mongo = new \MongoDB\Driver\Manager('mongodb://localhost:27017');

$res = mongoItemListSQLSelect($mongo, $sql);

var_dump($res);
