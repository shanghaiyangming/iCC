<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
        
        // 绑定响应返回json的策略
        $view = $locator->get('Zend\View\View');
        $strategy = $locator->get('ViewJsonStrategy');
        $view->getEventManager()->attach($strategy, 100);
        
        // 渲染结束绑定onRenderError事件
        $events = $e->getTarget()->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array(
            $this,
            'onRenderError'
        ));
        
        // 绑定缓存事件
//         $cache = $locator->get(CACHE_ADAPTER);
//         $cacheListenerAggregate = new CacheListenerAggregate($cache);
//         $cacheListenerAggregate->attach($eventManager);
//         GlobalEventManager::setEventCollection($eventManager);
        // 也可以使用\Zend\EventManager\StaticEventManager来实现事件的全局化
        
        //开启FirePHP调试或者关闭
        \FirePHP::getInstance(true)->setEnabled(true);
    }


}