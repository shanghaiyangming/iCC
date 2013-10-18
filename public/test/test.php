<?php
include 'iDatabase.php';
header("Content-type: text/html; charset=utf-8");
echo '<pre>';
try {
    
    $dns = array(
        'default' => array(
            'project_id' => '525373b3489619984c30d8e1',
            'password' => '525373b3489619984c30d8e1'
        )
    );
    
    iDatabase::getInstance($dns);
    $dbName = 'default';
    $form = 'iPrivileges_role';
    $query = array();
    
    //var_dump(iDatabase::findOne($dbName,$form, $query));
    for ($i = 0; $i < 100; $i ++) {
        echo $i;
        echo "<br />";
        var_dump(iDatabase::findOne($dbName,$form, $query));
        var_dump(iDatabase::findAll($dbName,'iPrivileges_resource', $query));
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
}
