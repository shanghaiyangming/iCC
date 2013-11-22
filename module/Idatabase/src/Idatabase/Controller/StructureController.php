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

class StructureController extends BaseActionController
{

    private $_project_id;

    private $_collection_id;

    private $_structure;

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
        return $this->findAll(IDATABASE_STRUCTURES, $query);
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
        $datas['searchable'] = $this->params()->fromPost('searchable', false);
        $datas['main'] = $this->params()->fromPost('main', false);
        $datas['required'] = $this->params()->fromPost('required', false);
        $datas['rshForm'] = $this->params()->fromPost('rshForm', false);
        $datas['rshType'] = $this->params()->fromPost('rshType', false);
        $datas['rshKey'] = $this->params()->fromPost('rshKey', false);
        $datas['rshValue'] = $this->params()->fromPost('rshValue', false);
        $datas['showImage'] = $this->params()->fromPost('showImage', false);
        $datas['orderBy'] = $this->params()->fromPost('orderBy', 0);
        
        if ($datas['field'] == null) {
            return $this->msg(false, '请填写字段名称');
        }
        
        if ($datas['label'] == null) {
            return $this->msg(false, '请填写字段描述');
        }
        
        if ($datas['type'] == null) {
            return $this->msg(false, '请选择字段类型');
        }
        
        if ($this->checkExist($datas['field'])) {
            return $this->msg(false, '字段名称已经存在');
        }
        
        if ($this->checkExist($datas['label'])) {
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
        $datas = array();
        $datas['collection_id'] = $this->_collection_id;
        $datas['field'] = $this->params()->fromPost('field', null);
        $datas['label'] = $this->params()->fromPost('label', null);
        $datas['type'] = $this->params()->fromPost('type', null);
        $datas['searchable'] = $this->params()->fromPost('searchable', false);
        $datas['main'] = $this->params()->fromPost('main', false);
        $datas['required'] = $this->params()->fromPost('required', false);
        $datas['rshForm'] = $this->params()->fromPost('rshForm', false);
        $datas['rshType'] = $this->params()->fromPost('rshType', false);
        $datas['rshKey'] = $this->params()->fromPost('rshKey', false);
        $datas['rshValue'] = $this->params()->fromPost('rshValue', false);
        $datas['showImage'] = $this->params()->fromPost('showImage', false);
        $datas['orderBy'] = $this->params()->fromPost('orderBy', 0);
        
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
        
        if ($this->checkExist($datas['field']) && $oldStructureInfo['field'] != $datas['field']) {
            return $this->msg(false, '字段名称已经存在');
        }
        
        if ($this->checkExist($datas['label']) && $oldStructureInfo['label'] != $datas['label']) {
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
                '_id' => myMongoId($row)
            ));
        }
        return $this->msg(true, '删除信息成功');
    }

    /**
     * 检测一个项目是否存在，根据名称和编号
     *
     * @param string $info            
     * @return boolean
     */
    private function checkExist($info)
    {
        $info = $this->_structure->findOne(array(
            '$or' => array(
                array(
                    'field' => $info
                ),
                array(
                    'label' => $info
                )
            )
        ));
        
        if ($info == null) {
            return false;
        }
        return true;
    }
}
