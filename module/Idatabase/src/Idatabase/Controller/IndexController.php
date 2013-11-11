<?php
/**
 * iDatabase索引控制器
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;
use My\Common\ActionController;

class IndexController extends ActionController
{
    private $_model;
    
    public function init() {
        $this->_model = $this->model(IDATABASE_INDEXES);
    }

    /**
     * 获取全部索引信息
     * @author young
     * @name ICC系统主控制面板
     * @version 2013.11.11 young
     */
    public function indexAction()
    {
        return $this->findAll(IDATABASE_INDEXES, array());
    }
    
    public function ensureIndexAction() {
        
    }
    
    public function deleteIndexAction() {
        
    }
    
    public function deleteIndexesAction() {
        
    }
    
    public function getIndexInfoAction() {
        
    }
}
