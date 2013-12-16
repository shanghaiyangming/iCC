<?php
date_default_timezone_set('Asia/Shanghai');
ini_set('mongo.native_long',1);
chdir(dirname(__DIR__));
require 'init_autoloader.php';
Zend\Mvc\Application::init(require 'config/application.config.php')->run();