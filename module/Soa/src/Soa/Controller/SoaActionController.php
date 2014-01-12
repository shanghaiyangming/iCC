<?php
/**
 * Soa基础类库
 *
 * @author young 
 * @version 2013.12.22
 * 
 */
namespace Soa\Controller;

use My\Common\Controller\Action;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\Mvc\MvcEvent;

abstract class SoaActionController extends Action
{

    protected $project_id;

    protected $collection_id;

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

        });
        
        parent::__construct();
    }
}
