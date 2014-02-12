<?php
/**
 * 应用框架设定
 * @author ming
 *
 */
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

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

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $locator = $app->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $this->initFirePHP();
        $this->initAuthentication();
        
        // 微软这个流氓，低于IE10版本一下的IE浏览器都需要使用text/html格式的Response，否则json在浏览器中会提示下载
        $eventManager->attach(MvcEvent::EVENT_RENDER, function ($event)
        {
            $objHeaders = $event->getResponse()
                ->getHeaders();
            $contentType = $objHeaders->get('Content-Type');
            if ($contentType) {
                $contentType = $contentType->getFieldValue();
                if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($contentType, 'application/json') !== false) {
                    $objHeaders->addHeaderLine('Content-Type', 'text/html;charset=utf-8');
                }
            }
            else {
                $objHeaders->addHeaderLine('Content-Type', 'text/html;charset=utf-8');
            }
        }, - 10000);
    }

    public function initFirePHP()
    {
        // 开启FirePHP调试或者关闭
        \FirePHP::getInstance(true)->setEnabled(true);
    }

    public function initAuthentication()
    {
        // 初始化授权
        $auth = new AuthenticationService();
        $auth->setStorage(new SessionStorage('account'));
    }
}