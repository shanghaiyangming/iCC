<?php
/**
 * iDatabase集合映射管理系统
 *
 * @author young 
 * @version 2014.01.02
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class MappingController extends BaseActionController
{

    private $_mapping;

    private $_project_id;

    private $_collection_id;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_mapping = $this->model(IDATABASE_MAPPING);
    }

    /**
     * 读取映射关系
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2013.11.19 young
     */
    public function indexAction()
    {
        $query = array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id
        );
        return $this->findAll(IDATABASE_MAPPING, $query);
    }

    /**
     * 更新映射关系
     *
     * @author young
     * @name 更新映射关系
     * @version 2014.01.02 young
     * @return JsonModel
     */
    public function updateAction()
    {
        $collection = $this->params()->fromPost('collection', '');
        $database = $this->params()->fromPost('database', DEFAULT_DATABASE);
        $cluster = $this->params()->fromPost('cluster', DEFAULT_CLUSTER);
        
        $criteria = array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id
        );
        
        $datas = array(
            'collection' => $collection,
            'database' => $database,
            'cluster' => $cluster
        );
        
        $rst = $this->_mapping->update($criteria, array(
            '$set' => $datas
        ), array(
            'upsert' => true
        ));
        
        if ($rst['ok']) {
            return $this->msg(true, '设定映射关系成功');
        } else {
            return $this->msg(false, Json::encode($rst));
        }
    }
}
