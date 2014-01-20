<?php
date_default_timezone_set('Asia/Shanghai');
ini_set('mongo.native_long', 1);
ini_set('display_errors ', 1);
chdir(dirname(__DIR__));
require 'init_autoloader.php';
try {
    Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (\Exception $e) {
    var_dump($e->getTraceAsString());
}