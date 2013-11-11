<?php
/**
 * iDatabase基础类库
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\Mvc\MvcEvent;

abstract class BaseActionController extends ActionController
{

    protected $project_id;

    protected $collection_id;

    protected $user_id;

    public function __construct()
    {
        // 增加iDatabase模块的公共方法
        $eventManager = $this->getEventManager();
        $serviceLocator = $this->getServiceLocator();
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function ($event) use($serviceLocator)
        {
            $this->project_id = $this->params()
                ->fromQuery('project_id', false) ? $this->params()
                ->fromPost('project_id', null) : $this->params()
                ->fromQuery('project_id', null);
            
            $this->collection_id = $this->params()
                ->fromQuery('collection_id', false) ? $this->params()
                ->fromPost('collection_id', null) : $this->params()
                ->fromQuery('collection_id', null);
            
            //身份验证不通过的情况下，执行以下操作
            if (false) {
                $event->stopPropagation(true);
                $event->setViewModel($this->msg(false, 'exit'));
            }
        });
        
        parent::__construct();
    }
}
