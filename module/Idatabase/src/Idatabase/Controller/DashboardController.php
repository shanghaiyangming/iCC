<?php
/**
 * iDatabase仪表盘控制，显示宏观统计视图
 *
 * @author young 
 * @version 2014.02.10
 * 
 */
namespace Idatabase\Controller;

use My\Common\Controller\Action;
use My\Common\Queue;
use Zend\Json\Json;

class DashboardController extends Action
{

    private $_statistic;

    private $_project;

    private $_collection;

    private $_project_id;

    private $_mapping;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['__PROJECT_ID__']) ? trim($_REQUEST['__PROJECT_ID__']) : '';
        $this->_collection = $this->model('Idatabase\Model\Collection');
        $this->_statistic = $this->model('Idatabase\Model\Statistic');
        $this->_mapping = $this->model('Idatabase\Model\Mapping');
    }

    /**
     * IDatabase仪表盘显示界面
     *
     * @author young
     * @name IDatabase仪表盘显示界面
     * @version 2013.11.11 young
     */
    public function indexAction()
    {
        $rst = array();
        $statistics = $this->_statistic->getAllStatisticsByProject($this->_project_id);
        foreach ($statistics as $statistic) {
            if (! empty($statistic['dashboardOut'])) {
                $model = $this->collection($statistic['dashboardOut'], DB_MAPREDUCE, DEFAULT_CLUSTER);
                $datas = $model->findAll(array(), array(
                    '$natural' => 1
                ), 0, 100);
                $statistic['__DATAS__'] = $datas;
                $rst[] = $statistic;
            }
        }
        echo Json::encode($rst);
        return $this->response;
    }

    /**
     * 逐一统计所有需要统计的脚本信息
     * 脚本执行方法: php index.php statistics run
     * @throws \Exception
     */
    public function runAction()
    {
        $logError = function ($statisticInfo, $rst)
        {
            $this->_statistic->update(array(
                '_id' => $statisticInfo['_id']
            ), array(
                '$set' => array(
                    'dashboardError' => is_string($rst) ? $rst : Json::encode($rst)
                )
            ));
        };
        
        $statistics = $this->_statistic->findAll(array());
        foreach ($statistics as $statisticInfo) {
            try {
                if (! empty($statisticInfo['dashboardOut'])) {
                    $oldDashboardOut = $this->collection($statisticInfo['dashboardOut'], DB_MAPREDUCE, DEFAULT_CLUSTER);
                    $oldDashboardOut->physicalDrop();
                }
                
                $dataModel = $this->collection(iCollectionName($statisticInfo['collection_id']));
                $query = array();
                if (! empty($statisticInfo['dashboardQuery'])) {
                    $query['$and'][] = $statisticInfo['dashboardQuery'];
                }
                $query['$and'][] = array(
                    '__CREATE_TIME__' => array(
                        '$gte' => new \MongoDate(time() - $statisticInfo['statisticPeriod'])
                    )
                );
                
                $rst = mapReduce($dataModel, $statisticInfo, $query, 'reduce');
                if ($rst instanceof \MongoCollection) {
                    $outCollectionName = $rst->getName(); // 输出集合名称
                    $this->_statistic->update(array(
                        '_id' => $statisticInfo['_id']
                    ), array(
                        '$set' => array(
                            'dashboardOut' => $outCollectionName,
                            'lastExecuteTime' => new \MongoDate(),
                            'resultExpireTime' => new \MongoDate(time() + $info['interval'])
                        )
                    ));
                } else {
                    $logError($statisticInfo, $rst);
                }
            } catch (\Exception $e) {
                $logError($statisticInfo, $e->getMessage());
            }
        }
        
        echo 'OK';
        return $this->response;
    }
}
