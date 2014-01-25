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
        $name = trim($this->params()->fromPost('name', ''));
        $yAxisTitle = trim($this->params()->fromPost('yAxisTitle', ''));
        $yAxisType = trim($this->params()->fromPost('yAxisType', ''));
        $yAxisFields = trim($this->params()->fromPost('yAxisFields', ''));
        $xAxisTitle = trim($this->params()->fromPost('xAxisTitle', ''));
        $xAxisType = trim($this->params()->fromPost('xAxisType', ''));
        $xAxisFields = trim($this->params()->fromPost('xAxisFields', ''));
        $seriesType = trim($this->params()->fromPost('seriesType', ''));
        $seriesField = trim($this->params()->fromPost('seriesField', ''));//用于pie
        $seriesXField = trim($this->params()->fromPost('seriesXField', ''));//用于x轴显示
        $seriesYField = trim($this->params()->fromPost('seriesYField', ''));//用于y轴显示
        $interval = intval($this->params()->fromPost('interval', 300));
        
        if ($name == null) {
            return $this->msg(false, '请填写统计名称');
        }
        
        if ($interval <= 300) {
            return $this->msg(false, '统计时间的间隔不得少于300秒');
        }
        
        $yAxisFields = explode(',',$yAxisFields);
        $xAxisFields = explode(',',$xAxisFields);
        
        $datas = array();
        $datas['name'] = $name;
        $datas['yAxis']['title'] = $yAxisTitle; // title string
        $datas['yAxis']['type'] = $yAxisType; // [Numeric]
        $datas['yAxis']['fields'] = $yAxisFields; // array()
        $datas['yAxis']['title'] = $xAxisTitle; // title string
        $datas['xAxis']['type'] = $xAxisType; // [Category|Time]
        $datas['xAxis']['fields'] = $xAxisFields; // array()
        $datas['series']['type'] = $seriesType; // [line|column]
        $datas['series']['xField'] = $seriesXField;
        $datas['series']['yField'] = $seriesYField;
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
