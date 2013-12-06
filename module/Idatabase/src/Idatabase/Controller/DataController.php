<?php

/**
 * iDatabase数据管理控制器
 *
 * @author young 
 * @version 2013.11.22
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class DataController extends BaseActionController
{

    private $_data;

    private $_structure;

    private $_project_id;

    private $_collection_id;

    private $_collection_name;

    private $_schema;

    private $_order;

    /**
     * 初始化函数
     * 
     * @see \My\Common\ActionController::init()
     */
    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id)) {
            throw new \Exception('$this->_project_id值未设定');
        }
        
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id)) {
            throw new \Exception('$this->_collection_id值未设定');
        }
        
        $this->_collection_name = 'idatabase_collection_' . $this->_collection_id;
        $this->_data = $this->model($this->_collection_name);
        $this->_structure = $this->model(IDATABASE_STRUCTURES);
        
        $this->_schema = $this->getSchema();
        $this->_order = $this->model(IDATABASE_COLLECTION_ORDERBY);
    }

    /**
     * 读取指定项目内的全部集合列表
     * 支持专家模式和普通模式显示，对于一些说明表和关系表，请在定义时，定义为普通模式
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2013.11.19 young
     */
    public function indexAction()
    {
        $query = array();
        
        $action = $this->params()->fromQuery('action', null);
        if ($action == 'search') {
            
        }
        
        if (empty($sort)) {
            $sort = $this->defaultOrder();
        }
        return $this->findAll($this->_collection_name, $query, $sort);
    }

    /**
     * 添加新数据
     *
     * @author young
     * @name 添加新数据
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function addAction()
    {
        $datas = array();
        $datas = array_intersect_key($_POST, $this->_schema['post']);
        $files = array_intersect_key($_FILES, $this->_schema['file']);
        
        if (empty($datas))
            return $this->msg(false, '提交数据中未包含有效字段');
        
        if (! empty($files)) {
            foreach ($_FILES as $fieldName => $file) {
                $fileInfo = $this->_data->storeToGridFS($fieldName);
                if (isset($fileInfo['_id']) && $fileInfo['_id'] instanceof \MongoId)
                    $datas[$fieldName] = $fileInfo['_id']->__toString();
                else
                    throw new \Exception('文件存储未成功' . json_encode($fileInfo));
            }
        }
        
        $datas = $this->dealData($datas);
        $this->_data->insertByFindAndModify($datas);
        return $this->msg(true, '提交数据成功');
    }

    /**
     * 编辑新的集合信息
     *
     * @author young
     * @name 编辑新的集合信息
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function editAction()
    {
        $datas = array();
        $datas = array_intersect_key($_POST, $this->_schema['post']);
        $files = array_intersect_key($_FILES, $this->_schema['file']);
        
        if (empty($datas))
            return $this->msg(false, '提交数据中未包含有效字段');
        
        if (! empty($files)) {
            foreach ($_FILES as $fieldName => $file) {
                $fileInfo = $this->_data->storeToGridFS($fieldName);
                if (isset($fileInfo['_id']) && $fileInfo['_id'] instanceof \MongoId)
                    $datas[$fieldName] = $fileInfo['_id']->__toString();
                else
                    throw new \Exception('文件存储未成功' . json_encode($fileInfo));
            }
        }
        
        $datas = $this->dealData($datas);
        $this->_data->update(array(
            '_id' => myMongoId($datas['_id'])
        ), array(
            '$set' => $datas
        ));
        
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 删除数据
     *
     * @author young
     * @name 删除数据
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function removeAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        try {
            $_id = Json::decode($_id, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return $this->msg(false, '无效的json字符串');
        }
        
        if (! is_array($_id)) {
            return $this->msg(false, '请选择你要删除的项');
        }
        foreach ($_id as $row) {
            $this->_data->remove(array(
                '_id' => myMongoId($row)
            ));
        }
        return $this->msg(true, '删除信息成功');
    }

    /**
     * 获取集合的数据结构
     *
     * @return array
     */
    private function getSchema()
    {
        $schema = array();
        $schema['post']['_id'] = array(
            'field' => '_id',
            'label' => '_id',
            'type' => '_id'
        );
        
        $cursor = $this->_structure->find(array(
            'collection_id' => $this->_collection_id
        ));
        $cursor->sort(array(
            'orderBy' => 1,
            '_id' => - 1
        ));
        
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $type = $row['type'] == 'filefield' ? 'file' : 'post';
            $schema[$type][$row['field']] = $row;
        }
        return $schema;
    }

    /**
     * 处理入库的数据
     *
     * @param array $datas            
     * @return array
     */
    private function dealData($datas)
    {
        $validData = array_intersect_key($datas, $this->_schema['post']);
        array_walk($validData, function (&$value, $key)
        {
            $value = filter_var($value, $this->_schema['post'][$key]['filter']);
            switch ($this->_schema[$key]['type']) {
                case 'numberfield':
                    $value = preg_match("/^[0-9]+\.[0-9]+$/", $value) ? floatval($value) : intval($value);
                    break;
                case 'datefield':
                    $value = preg_match("/^[0-9]+$/", $value) ? new MongoDate(intval($value)) : new MongoDate(strtotime($value));
                    break;
                default:
                    $value = trim($value);
                    break;
            }
        });
        return $validData;
    }

    /**
     * 获取当前集合的排列顺序
     *
     * @return array
     */
    private function defaultOrder()
    {
        $cursor = $this->_order->find(array(
            'collection_id' => $this->_collection_id
        ));
        $cursor->sort(array(
            'priority' => - 1,
            '_id' => - 1
        ));
        
        $order = array();
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $order[$row['field']] = $row['order'];
        }
        
        if (!isset($order['_id'])) {
            $order['_id'] = - 1;
        }
        return $order;
    }
}
