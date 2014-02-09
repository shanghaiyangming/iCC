<?php
/**
 * iDatabase仪表盘控制，显示宏观统计视图
 *
 * @author young 
 * @version 2013.11.11
 * 
 */
namespace Idatabase\Controller;

use My\Common\Controller\Action;
use My\Common\Queue;

class DashboardController extends Action
{

    private $_statistic;

    private $_project;

    private $_collection;

    private $_project_id;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['__PROJECT_ID__']) ? trim($_REQUEST['__PROJECT_ID__']) : '';
        $this->_collection = $this->model('Idatabase\Model\Collection');
        $this->_statistic = $this->model('Idatabase\Model\Statistic');
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
        $dashboard = new Queue();
        $cursor = $this->_collection->find(array(
            'project_id' => $this->_project_id
        ));
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $collection_id = $row['_id']->__toString();
            $statisticInfos = $this->_statistic->findAll(array(
                'collection_id' => $collection_id
            ));
            if (! empty($statisticInfos)) {
                foreach ($statisticInfos as $statisticInfos) {
                    $dashboard->insert($statisticInfos, $statisticInfos['priority']);
                }
            }
        }
        
        $datas = array();
        if (! $dashboard->isEmpty()) {
            $dashboard->top();
            while ($dashboard->valid()) {
                $datas[] = $dashboard->current();
                $dashboard->next();
            }
        }
        
        return $this->rst($datas, $dashboard->count(), true);
    }

    public function runAction()
    {
        $statistic_id = $this->params()->fromQuery('__STATISTIC_ID__', null);
        if (empty($statistic_id)) {
            throw new \Exception('请选择统计方法');
        }
        
        $info = $this->_statistic->findOne(array(
            '_id' => myMongoId($statistic_id)
        ));
        if ($info == null) {
            throw new \Exception('统计方法不存在');
        }
        
        $query = array();
        $query['$and'][] = $info['dashboardQuery'];
        $query['$and'][] = array(
            '__CREATE_TIME__' => array(
                '$gte' => new \MongoDate(time() - $info['statisticPeriod'])
            )
        );
        
        $rst = $this->mapreduce($info, $query, 'reduce');
        if (! $rst instanceof \MongoCollection) {
            return $this->deny('$rst不是MongoCollection的子类实例');
            throw new \Exception('$rst不是MongoCollection的子类实例');
        }
        
        $outCollectionName = $rst->getName(); // 输出集合名称
        $this->_statistic->update(array(
            '_id' => myMongoId($statistic_id)
        ), array(
            '$set' => array(
                'dashboardOut' => $outCollectionName,
                'lastExecuteTime' => new \MongoDate(),
                'resultExpireTime' => new \MongoDate(time() + $info['interval'])
            )
        ));
        $limit = intval($info['maxShowNumber']) > 0 ? intval($info['maxShowNumber']) : 100;
        $datas = $rst->findAll(array(), array(
            'value' => - 1
        ), 0, $limit);
        return $this->rst($datas, 0, true);
    }
}
