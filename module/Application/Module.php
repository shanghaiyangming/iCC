<?php
/**
 * 
 * 框架
 * @author ming
 *
 */
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use My\Common\CacheListenerAggregate;
use Zend\EventManager\GlobalEventManager;
use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;

class Module
{

    /**
     * 加载配置信息
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getConsoleUsage(AdapterInterface $console)
    {}

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $locator = $app->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        //开启FirePHP调试或者关闭
        \FirePHP::getInstance(true)->setEnabled(true);
    }


}