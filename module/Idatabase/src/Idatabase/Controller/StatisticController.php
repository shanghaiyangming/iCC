<?php
/**
 * iDatabase项目内数据集合管理
 *
 * @author young 
 * @version 2013.11.19
 * 
 */
namespace Idatabase\Controller;

use My\Common\Controller\Action;

class StatisticController extends Action
{

    private $_collection;

    private $_collection_id;

    private $_project_id;

    private $_statistic;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['__PROJECT_ID__']) ? trim($_REQUEST['__PROJECT_ID__']) : '';
        $this->_collection_id = isset($_REQUEST['__COLLECTION_ID__']) ? trim($_REQUEST['__COLLECTION_ID__']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_statistic = $this->model('Idatabase\Model\Statistic');
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
        $query = array(
            array(
                'collection_id' => $this->_collection_id
            )
        );
        $datas = $this->_statistic->findAll($query);
        return $this->rst($datas, 0, true);
    }

    /**
     *
     * @return array
     */
    public function addAction()
    {
        $name = $this->params()->fromPost('name', null);
        $type = $this->params()->fromPost('type', null);
        $axes = $this->params()->fromPost('axes', null);
        $series = $this->params()->fromPost('series', null);
        $interval = intval($this->params()->fromPost('interval', 0));
        
        if ($name == null) {
            return $this->msg(false, '请填写统计名称');
        }
        
        if ($type == null) {
            return $this->msg(false, '请选择统计类型');
        }
        
        if ($interval <= 300) {
            return $this->msg(false, '统计时间的间隔不得少于300秒');
        }
        
        $datas = array();
        $datas['name'] = $name;
        $datas['yAxis']['type'] = $yAxisType; //[Numeric]
        $datas['yAxis']['fields'] = array();
        $datas['xAxis']['type'] = '';
        $datas['xAxis']['fields'] = array();
        $datas['series']['type'] = $ySeriesType;//[line|column]
        $datas['series']['xField'] = $ySeriesXField;
        $datas['series']['yField'] = $ySeriesXField;
        $datas['ySeriesType'];
        
        $datas['axes'] = $axes;
        $datas['series'] = $series;
        $datas['interval'] = $interval;
        $datas['lastExecuteTime'] = new \MongoDate(0);
        $datas['resultExpireTime'] = new \MongoDate(0 + $interval);
        $datas['isRunning'] = false;
        $this->_statistic->insert($datas);
        
        return $this->msg(true, '添加统计成功');
    }

    /**
     *
     * @return array
     */
    public function editAction()
    {}
}
