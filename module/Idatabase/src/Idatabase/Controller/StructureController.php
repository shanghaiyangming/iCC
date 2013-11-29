<?php
/**
 * iDatabase定义数据字典
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
use Zend\Json\Json;

class StructureController extends BaseActionController
{

    private $_project_id;

    private $_collection_id;

    private $_structure;

    private $_model;

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
        
        $this->_structure = $this->model(IDATABASE_STRUCTURES);
        
        $this->_model = $this->_structure;
    }

    /**
     * 读取某个集合的全部字段
     *
     * @author young
     * @name 读取某个集合的全部字段
     * @version 2013.11.22 young
     */
    public function indexAction()
    {
        $query = array(
            'collection_id' => $this->_collection_id
        );
        return $this->findAll(IDATABASE_STRUCTURES, $query, array(
            'orderBy' => 1,
            '_id' => 1
        ));
    }

    /**
     * 添加新的字段
     *
     * @author young
     * @name 添加新的字段
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function addAction()
    {
        $datas = array();
        $datas['collection_id'] = $this->_collection_id;
        $datas['field'] = $this->params()->fromPost('field', null);
        $datas['label'] = $this->params()->fromPost('label', null);
        $datas['type'] = $this->params()->fromPost('type', null);
        $datas['searchable'] = filter_var($this->params()->fromPost('searchable', false), FILTER_VALIDATE_BOOLEAN);
        $datas['main'] = filter_var($this->params()->fromPost('main', false), FILTER_VALIDATE_BOOLEAN);
        $datas['required'] = filter_var($this->params()->fromPost('required', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshCollection'] = $this->params()->fromPost('rshCollection', '');
        $datas['rshType'] = $this->params()->fromPost('rshType', '');
        $datas['rshKey'] = filter_var($this->params()->fromPost('rshKey', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshValue'] = filter_var($this->params()->fromPost('rshValue', false), FILTER_VALIDATE_BOOLEAN);
        $datas['showImage'] = filter_var($this->params()->fromPost('showImage', false), FILTER_VALIDATE_BOOLEAN);
        $datas['orderBy'] = filter_var($this->params()->fromPost('orderBy', 0), FILTER_VALIDATE_INT);
        
        if ($datas['field'] == null) {
            return $this->msg(false, '请填写字段名称');
        }
        
        if ($datas['label'] == null) {
            return $this->msg(false, '请填写字段描述');
        }
        
        if ($datas['type'] == null) {
            return $this->msg(false, '请选择字段类型');
        }
        
        if ($this->checkExist('field', $datas['field'], array(
            'collection_id' => $this->_collection_id
        ))) {
            return $this->msg(false, '字段名称已经存在');
        }
        
        if ($this->checkExist('label', $datas['label'], array(
            'collection_id' => $this->_collection_id
        ))) {
            return $this->msg(false, '字段描述已经存在');
        }
        
        $this->_structure->insert($datas);
        
        return $this->msg(true, '添加信息成功');
    }

    /**
     * 编辑某些字段
     *
     * @author young
     * @name 编辑某些字段
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function editAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        $datas = array();
        $datas['collection_id'] = $this->_collection_id;
        $datas['field'] = $this->params()->fromPost('field', null);
        $datas['label'] = $this->params()->fromPost('label', null);
        $datas['type'] = $this->params()->fromPost('type', null);
        $datas['searchable'] = filter_var($this->params()->fromPost('searchable', false), FILTER_VALIDATE_BOOLEAN);
        $datas['main'] = filter_var($this->params()->fromPost('main', false), FILTER_VALIDATE_BOOLEAN);
        $datas['required'] = filter_var($this->params()->fromPost('required', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshCollection'] = $this->params()->fromPost('rshCollection', '');
        $datas['rshType'] = $this->params()->fromPost('rshType', '');
        $datas['rshKey'] = filter_var($this->params()->fromPost('rshKey', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshValue'] = filter_var($this->params()->fromPost('rshValue', false), FILTER_VALIDATE_BOOLEAN);
        $datas['showImage'] = filter_var($this->params()->fromPost('showImage', false), FILTER_VALIDATE_BOOLEAN);
        $datas['orderBy'] = filter_var($this->params()->fromPost('orderBy', 0), FILTER_VALIDATE_INT);
        
        if ($datas['field'] == null) {
            return $this->msg(false, '请填写字段名称');
        }
        
        if ($datas['label'] == null) {
            return $this->msg(false, '请填写字段描述');
        }
        
        if ($datas['type'] == null) {
            return $this->msg(false, '请选择字段类型');
        }
        
        $oldStructureInfo = $this->_structure->findOne(array(
            '_id' => myMongoId($_id)
        ));
        
        if ($this->checkExist('field', $datas['field'], array(
            'collection_id' => $this->_collection_id
        )) && $oldStructureInfo['field'] != $datas['field']) {
            return $this->msg(false, '字段名称已经存在');
        }
        
        if ($this->checkExist('label', $datas['label'], array(
            'collection_id' => $this->_collection_id
        )) && $oldStructureInfo['label'] != $datas['label']) {
            return $this->msg(false, '字段描述已经存在');
        }
        
        $this->_structure->update(array(
            '_id' => myMongoId($_id)
        ), array(
            '$set' => $datas
        ));
        
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 删除某些字段
     *
     * @author young
     * @name 删除某些字段
     * @version 2013.11.22 young
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
            $this->_structure->remove(array(
                '_id' => myMongoId($row),
                'collection_id' => $this->_collection_id
            ));
        }
        return $this->msg(true, '删除字段属性成功');
    }

    /**
     * 检测字段是否已经存在
     *
     * @param string $field            
     * @param string $info            
     * @param array $extra            
     * @param resource $model            
     * @return boolean
     */
    private function checkExist($field, $info, $extra = null, $model = null)
    {
        if ($model == null) {
            if ($this->_model instanceof \MongoCollection) {
                $model = $this->_model;
            } else {
                throw new \Exception('$this->_model未设定');
            }
        }
        
        $query = array();
        if (empty($extra) || ! is_array($extra)) {
            $query = array(
                $field => $info
            );
        } else {
            $query = array(
                '$and' => array(
                    array(
                        $field => $info
                    ),
                    $extra
                )
            );
        }
        $info = $model->findOne($query);
        
        if ($info == null) {
            return false;
        }
        return true;
    }
}
