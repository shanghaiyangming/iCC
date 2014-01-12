<?php
namespace My\Common\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;

abstract class Action extends AbstractActionController
{

    protected $controller;

    protected $action;

    public function __construct()
    {
        // 确保输出为UTF-8
        header('Content-type:text/html;charset=utf-8');
        
        // 增加权限控制方法在这里
        
        // 添加初始化事件函数
        $eventManager = $this->getEventManager();
        $serviceLocator = $this->getServiceLocator();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function () use($serviceLocator)
        {
            $this->preDispatch();
            if (method_exists($this, 'init')) {
                $this->init();
            }
        }, 200);
    }

    public function preDispatch()
    {
        $routerMatch = $this->getEvent()->getRouteMatch();
        $this->action = $routerMatch->getParam('action', null);
        $this->controller = $routerMatch->getParam('controller', null);
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
     * @param boolean $jsonModel            
     * @param string $jsonpCallback            
     * @throws \Exception
     * @return \Zend\View\Model\JsonModel Ambigous multitype:multitype: string >
     */
    public function findAll($collection, $query = array(), $sort = array('_id'=>-1), $jsonModel = true, $jsonpCallback = null)
    {
        $model = $this->model($collection);
        $cursor = $model->find($query);
        if (! $cursor instanceof \MongoCursor)
            throw new \Exception('$query error:' . json_encode($query));
        
        $cursor->sort($sort);
        $skip = (int) $this->params()->fromQuery('start', 0);
        if ($skip > 0) {
            $cursor->skip($skip);
        }
        $limit = (int) $this->params()->fromQuery('limit', 0);
        if ($limit > 0) {
            $cursor->limit($limit);
        }
        
        $datas = iterator_to_array($cursor, false);
        if ($jsonModel) {
            if ($jsonpCallback == null) {
                header('Content-Type: application/json; charset=utf-8');
                return new JsonModel($this->rst($datas));
            } else {
                header('Content-Type: application/javascript; charset=utf-8');
                $obj = new JsonModel($this->rst($datas));
                $obj->setJsonpCallback($jsonpCallback);
                return $obj;
            }
        } else
            return $datas;
    }

    /**
     * 返回结果集
     *
     * @param array $datas            
     * @param number $total            
     * @param boolean $jsonModel            
     * @param string $jsonpCallback            
     * @return \Zend\View\Model\JsonModel multitype:unknown
     */
    public function rst($datas, $total = 0, $jsonModel = false, $jsonpCallback = null)
    {
        $total = intval($total);
        $rst = array(
            'result' => $datas,
            'total' => $total ? $total : count($datas)
        );
        if ($jsonModel) {
            if ($jsonpCallback == null) {
                header('Content-Type: application/json; charset=utf-8');
                return new JsonModel($rst);
            } else {
                header('Content-Type: application/javascript; charset=utf-8');
                $obj = new JsonModel($rst);
                $obj->setJsonpCallback($jsonpCallback);
                return $obj;
            }
        }
        return $rst;
    }

    /**
     * 返回信息
     *
     * @param bool $status            
     * @param string $message            
     * @param string $jsonModel            
     * @return \Zend\View\Model\JsonModel multitype:unknown <boolean, unknown>
     */
    public function msg($status, $message, $jsonModel = true)
    {
        $rst = array(
            'success' => is_bool($status) ? $status : false,
            'msg' => $message
        );
        
        if ($jsonModel)
            return new JsonModel($rst);
        return $rst;
    }
}