<?php
include "iDatabase.php";

$collectionAlias = 'test';
$project_id = '52dfb57a1fd1be7809000029';
$password = '123123123123123123';
$key_id = '52fa47791fd1be600e000029';

$obj = new iDatabase($project_id, $password, $key_id);
$obj->setDebug(true);
$obj->setCollection($collectionAlias);
try {
    var_dump($obj->findAll(array()));
} catch (SoapFault $e) {
    var_dump($e);
}
echo 'end';
