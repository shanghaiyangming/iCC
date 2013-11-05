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

    public function getControllerConfig()
    {
        return array(
            'abstract_factories' => array(
                'My\Common\ControllerAbstractFactory'
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Admin\AuthenticationService' => 'Zend\Authentication\AuthenticationService'
            )
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $locator = $app->getServiceManager();
        $locator->get('mongos');
        
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
        $cache = $locator->get(CACHE_ADAPTER);
        $cacheListenerAggregate = new CacheListenerAggregate($cache);
        $cacheListenerAggregate->attach($eventManager);
        
        GlobalEventManager::setEventCollection($eventManager);
        
        //也可以使用\Zend\EventManager\StaticEventManager来实现事件的全局化
        
    }

    public function onRenderError($e)
    {
        // must be an error
        if (! $e->isError()) {
            return;
        }
        
        // Check the accept headers for application/json
        $request = $e->getRequest();
        if (! $request instanceof HttpRequest) {
            return;
        }
        
        $headers = $request->getHeaders();
        if (! $headers->has('Accept')) {
            return;
        }
        
        $accept = $headers->get('Accept');
        $match = $accept->match('application/json');
        if (! $match || $match->getTypeString() == '*/*') {
            // not application/json
            return;
        }
        
        // make debugging easier if we're using xdebug!
        ini_set('html_errors', 0);
        
        // if we have a JsonModel in the result, then do nothing
        $currentModel = $e->getResult();
        if ($currentModel instanceof JsonModel) {
            return;
        }
        
        // create a new JsonModel - use application/api-problem+json fields.
        $response = $e->getResponse();
        $model = new JsonModel(array(
            "httpStatus" => $response->getStatusCode(),
            "title" => $response->getReasonPhrase()
        ));
        
        // Find out what the error is
        $exception = $currentModel->getVariable('exception');
        
        if ($currentModel instanceof ModelInterface && $currentModel->reason) {
            switch ($currentModel->reason) {
                case 'error-controller-cannot-dispatch':
                    $model->detail = 'The requested controller was unable to dispatch the request.';
                    break;
                case 'error-controller-not-found':
                    $model->detail = 'The requested controller could not be mapped to an existing controller class.';
                    break;
                case 'error-controller-invalid':
                    $model->detail = 'The requested controller was not dispatchable.';
                    break;
                case 'error-router-no-match':
                    $model->detail = 'The requested URL could not be matched by routing.';
                    break;
                default:
                    $model->detail = $currentModel->message;
                    break;
            }
        }
        
        if ($exception) {
            if ($exception->getCode()) {
                $e->getResponse()->setStatusCode($exception->getCode());
            }
            $model->detail = $exception->getMessage();
            
            // find the previous exceptions
            $messages = array();
            while ($exception = $exception->getPrevious()) {
                $messages[] = "* " . $exception->getMessage();
            }
            if (count($messages)) {
                $exceptionString = implode("\n", $messages);
                $model->messages = $exceptionString;
            }
        }
        
        // set our new view model
        $model->setTerminal(true);
        $e->setResult($model);
        $e->setViewModel($model);
    }
}