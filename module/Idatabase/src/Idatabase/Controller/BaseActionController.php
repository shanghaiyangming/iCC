<?php
/**
 * iDatabase基础类库
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\Controller\Action;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\Mvc\MvcEvent;

abstract class BaseActionController extends Action
{

    protected $project_id;

    protected $collection_id;

    protected $user_id;

    protected $action;

    protected $controller;

    protected $module;

    public function __construct()
    {
        // 增加iDatabase模块的公共方法
        $eventManager = $this->getEventManager();
        $serviceLocator = $this->getServiceLocator();
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function ($event) use($serviceLocator)
        {
            // 身份验证不通过的情况下，执行以下操作
            if (!isset($_SESSION['account'])) {
                $event->stopPropagation(true);
                $event->setViewModel($this->msg(false, '未通过身份验证'));
            }
        });
        
        parent::__construct();
    }
}
