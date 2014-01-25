<?php
//启动session
session_start();

//PHP配置文件修改
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('mongo.native_long', 1);

//初始化应用程序
chdir(dirname(__DIR__));
require 'init_autoloader.php';
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
//Zend\Mvc\Application::init(require 'config/soa.config.php')->run();
