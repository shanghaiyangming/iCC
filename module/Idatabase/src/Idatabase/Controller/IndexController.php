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
use Zend\Json\Json;

class IndexController extends BaseActionController
{

    private $_model;

    private $_collection_id;

    private $_targetCollection;

    public function init()
    {
        $this->_model = $this->model(IDATABASE_INDEXES);
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id)) {
            throw new \Exception('$this->_collection_id值未设定');
        }
        
        $this->_targetCollection = $this->model('idatabase_collection_' . $this->_collection_id);
    }

    /**
     * 获取全部索引信息
     *
     * @author young
     * @name 数据集合的索引管理
     * @version 2013.11.11 young
     */
    public function indexAction()
    {
        return $this->findAll(IDATABASE_INDEXES, array(
            'collection_id' => $this->_collection_id
        ), array(
            '_id' => 1
        ));
    }

    /**
     * 添加数据集合的索引
     *
     * @author young
     * @name 添加数据集合的索引
     * @version 2013.12.22 young
     */
    public function addAction()
    {
        $keys = $this->params()->fromPost('keys', '');
        if (! isJson($keys)) {
            return $this->msg(false, 'keys必须符合json格式,例如：{"index_name":1,"2d":"2d"}');
        }
        $datas = array();
        $datas['keys'] = $keys;
        $datas['collection_id'] = $this->_collection_id;
        
        $keys = Json::decode($keys);
        if (! $this->_targetCollection->ensureIndex($keys, array(
            'background' => true
        ))) {
            return $this->msg(false, '创建索引失败');
        }
        $this->_model->insert($datas);
        return $this->msg(true, '创建索引成功');
    }

    /**
     * 删除数据集合的索引
     *
     * @author young
     * @name 删除数据集合的索引
     * @version 2013.12.22 young
     */
    public function removeAction()
    {
        $_id = myMongoId($this->params()->fromPost('_id', ''));
        $index = $this->_model->findOne(array(
            '_id' => $_id
        ));
        if ($index == null) {
            return $this->msg(false, '无效的索引');
        }
        $keys = Json::decode($index['keys']);
        if (! $this->_targetCollection->deleteIndex($keys)) {
            return $this->msg(false, '创建索引失败');
        }
        $this->_model->remove(array(
            '_id' => $_id
        ));
        return $this->msg(true, '删除索引成功');
    }
}
