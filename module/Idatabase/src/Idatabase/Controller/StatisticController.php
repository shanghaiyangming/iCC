<?php
/**
 * iDatabase项目内数据集合管理
 *
 * @author young 
 * @version 2013.11.19
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class StatisticController extends BaseActionController
{

    private $_collection;

    private $_collection_id;

    private $_project_id;

    private $_statistic;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_statistic = $this->model(IDATABASE_STATISTIC);
    }

    /**
     * 
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2014.01.09 young
     */
    public function indexAction()
    {}
}
