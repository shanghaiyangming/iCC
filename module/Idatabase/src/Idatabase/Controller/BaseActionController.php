<?php
/**
 * iDatabase基础公共类库
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\Mvc\MvcEvent;

abstract class BaseActionController extends ActionController
{

    private $project_id;

    private $collection_id;

    public function __construct()
    {
        // 增加iDatabase模块的公共方法
        $eventManager = $this->getEventManager();
        $serviceLocator = $this->getServiceLocator();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function () use($serviceLocator)
        {
            $this->project_id = $this->params()
                ->fromQuery('project_id', false) ? $this->params()
                ->fromPost('project_id', null) : $this->params()
                ->fromQuery('project_id', null);
            
            $this->collection_id = $this->params()
                ->fromQuery('collection_id', false) ? $this->params()
                ->fromPost('collection_id', null) : $this->params()
                ->fromQuery('collection_id', null);
        });
        
        parent::__construct();
    }
}
