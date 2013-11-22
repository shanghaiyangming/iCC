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
        
        // 注意这里应该增加检查，该项目id是否符合用户操作的权限范围
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
        $query = array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id
        );
        
        $search = $this->params()->fromQuery('search', null);
        if ($search != null) {
            $search = new \MongoRegex('/' . preg_replace("/[\s\r\t\n]/", '.*', $search) . '/i');
            $search = array(
                '$or' => array(
                    array(
                        'name' => $search
                    ),
                    array(
                        'sn' => $search
                    ),
                    array(
                        'desc' => $search
                    )
                )
            );
        }
        
        if ($search) {
            $query['$and'][] = $query;
            $query['$and'][] = $search;
        }
        
        return $this->findAll($this->_collection_name, $query);
    }

    /**
     * 添加新的数据
     *
     * @author young
     * @name 添加新的集合
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function addAction()
    {
        $datas = array();
        $this->_data->insertByFindAndModify($datas);
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
        $this->_collection->update(array(
            '_id' => myMongoId($_id)
        ), array(
            '$set' => $datas
        ));
        
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 删除新的项目
     *
     * @author young
     * @name 删除新的项目
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
            $this->_collection->remove(array(
                '_id' => myMongoId($row),
                'project_id' => $this->_project_id
            ));
        }
        return $this->msg(true, '删除信息成功');
    }

    /**
     * 检测一个集合是否存在，根据名称和编号
     *
     * @param string $info            
     * @return boolean
     */
    private function checkCollecionExist($info)
    {
        $info = $this->_collection->findOne(array(
            '$and' => array(
                array(
                    '$or' => array(
                        array(
                            'name' => $info
                        ),
                        array(
                            'alias' => $info
                        )
                    )
                ),
                array(
                    'project_id' => $this->_project_id
                )
            )
        ));
        
        if ($info == null) {
            return false;
        }
        return true;
    }
    
    private function getSchema() {
        $schema = array();
        $cursor = $this->_structure->find(array('collection_id'=>$this->_collection_id));
        $cursor->sort(array('priority'=>1));
        while($cursor->hasNext()) {
            $row = $cursor->getNext();
            $schema['field']
        }
        return $schema;
    }
}
