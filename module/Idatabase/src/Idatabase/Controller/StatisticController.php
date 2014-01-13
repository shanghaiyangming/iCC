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
     * 读取某个集合的全部统计
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2014.01.09 young
     */
    public function indexAction()
    {
        $query = array();
        $query = $this->_statistic->find(array(
            'collection_id' => $this->_collection_id
        ));
    }

    /**
     *
     * @return array
     */
    public function addAction()
    {
        $name = $this->params()->fromPost('name', null);
        $type = $this->params()->fromPost('type', null);
        $interval = intval($this->params()->fromPost('interval', 0));
        
        if ($name == null) {
            return $this->msg(false, '请填写统计名称');
        }
        
        if ($type == null) {
            return $this->msg(false, '请选择统计类型');
        }
        
        if ($interval <=300 ) {
            return $this->msg(false, '统计时间的间隔不得少于300秒');
        }
        
        $datas = array();
        $datas['name'] = $name;
        $datas['type'] = $type;
        $datas['axes']['left'] = $axes['left'];
        $datas['axes']['bottom'] = $axes['bottom'];
        $datas['series'] = $series;
        $datas['interval'] = $interval;
        $datas['lastExecuteTime'] = new \MongoDate(0);
        $datas['resultExpireTime'] = new \MongoDate(0 + $interval);
        $datas['isRunning'] = false;
        $this->_statistic->insert($datas);
        
        return $this->msg(true, '添加信息成功');
    }
    
    /**
     * 
     * @return array
     */
    public function editAction() {
        
    }
}
