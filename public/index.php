<?php
//启动session
session_start();

//PHP配置文件修改
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
ini_set('mongo.native_long', 1);
ini_set("display_errors", 1);

//初始化应用程序
chdir(dirname(__DIR__));
require 'init_autoloader.php';
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
