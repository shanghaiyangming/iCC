<?php
include "iDatabase.php";

$collectionAlias = 'iWeixin_application';
$project_id = '52dce3ab4a9619c12f8b4c7d';
$password = '11111111';
$key_id = '52fc9b2c499619b40d8bf47c';

$obj = new iDatabase($project_id, $password, $key_id);
$obj->setDebug(true);
$obj->setCollection($collectionAlias);
try {
    // var_dump($obj->findAll(array()));
    var_dump($obj->getSchema());
} catch (SoapFault $e) {
    var_dump($e);
}
echo 'end';
