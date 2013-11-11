<?php
namespace My\Common;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;
use Zend\View\View;

abstract class ActionController extends AbstractActionController
{

    public function __construct()
    {
        // 增加权限控制方法在这里
        
        // 添加初始化事件函数
        $em = $this->getEventManager();
        $em->attach(MvcEvent::EVENT_DISPATCH, function ()
        {
            if (method_exists($this, 'init'))
                $this->init();
        }, 200);
    }

    /**
     * 可以在controller中调用该方法，以便在执行action之前执行某些初始化的操作
     */
    public function init()
    {}

    /**
     * 获取指定集合的指定条件的全部数据
     * 默认返回json数组直接输出
     *
     * @param string $collection            
     * @param array $query            
     * @param array $sort            
     * @param bool $jsonModel            
     * @throws \Exception
     * @return \Zend\View\Model\JsonModel Ambigous multitype:multitype: string >
     */
    public function findAll($collection, $query = array(), $sort = array('_id'=>-1), $jsonModel = true)
    {
        $model = $this->model($collection);
        $cursor = $model->find($query);
        if (! $cursor instanceof \MongoCursor)
            throw new \Exception('$query error:' . json_encode($query));
        
        $cursor->sort($sort);
        $skip = (int) $this->params()->fromQuery('skip', 0);
        if ($skip > 0) {
            $cursor->skip($skip);
        }
        $limit = (int) $this->params()->fromQuery('limit', 0);
        if ($limit > 0) {
            $cursor->skip($limit);
        }
        
        $datas = convertToPureArray(iterator_to_array($cursor, false));
        if ($jsonModel)
            return new JsonModel($this->rst($datas));
        else
            return $datas;
    }

    /**
     * 返回结果集
     *
     * @param array $datas            
     * @return array
     */
    public function rst($datas)
    {
        return array(
            'result' => $datas,
            'total' => count($datas)
        );
    }

    /**
     * 返回信息
     *
     * @param bool $status            
     * @param string $message            
     * @return array
     */
    public function msg($status, $message)
    {
        return array(
            'success' => is_bool($status) ? $status : false,
            'msg' => $message
        );
    }
}