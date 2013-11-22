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
     * 读取某个集合的数字字典
     * 
     * @author young
     * @name 读取某个集合的数字字典
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
     * 添加新的集合属性
     *
     * @author young
     * @name 添加新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function addAction()
    {
        
        $datas['formId']      = $formId;
        $datas['name']        = $this->params()->fromPost('name', null);
        $datas['alias']       = $this->params()->fromPost('alias', null);
        $datas['type']        = $this->params()->fromPost('type', null);
        $datas['searchable']  = $this->params()->fromPost('searchable', false);
        $datas['main']        = $this->params()->fromPost('main', false);
        $datas['required']    = $this->params()->fromPost('required', false);
        $datas['rshForm']     = $this->params()->fromPost('rshForm', false);
        $datas['rshFormStructure'] = $this->params()->fromPost('rshForm', false);
        $datas['rshType']     = $this->params()->fromPost('rshType', false);
        $datas['rshKey']      = $this->params()->fromPost('rshKey', false);
        $datas['rshValue']    = $this->params()->fromPost('rshValue', false);
        $datas['showImage']    = (isset($_POST['showImage']) && trim($_POST['showImage'])=='false') ? false : true;
        $datas['orderBy']     = isset($_POST['orderBy']) ? intval($_POST['orderBy']) : 0;
        $datas['createTime']  = new MongoDate();
        
        $name = $this->params()->fromPost('name', null);
        $sn = $this->params()->fromPost('sn', null);
        $desc = $this->params()->fromPost('desc', null);
    
        if ($name == null) {
            return $this->msg(false, '请填写项目名称');
        }
    
        if ($sn == null) {
            return $this->msg(false, '请填写项目编号');
        }
    
        if ($desc == null) {
            return $this->msg(false, '请填写项目描述');
        }
    
        if ($this->checkProjectExist($name)) {
            return $this->msg(false, '项目名称已经存在');
        }
    
        if ($this->checkProjectExist($sn)) {
            return $this->msg(false, '项目编号已经存在');
        }
    
        $project = array();
        $project['name'] = $name;
        $project['sn'] = $sn;
        $project['desc'] = $desc;
        $this->_project->insert($project);
    
        return $this->msg(true, '添加信息成功');
    }
    
    /**
     * 编辑新的项目
     *
     * @author young
     * @name 编辑新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function editAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        $name = $this->params()->fromPost('name', null);
        $sn = $this->params()->fromPost('sn', null);
        $desc = $this->params()->fromPost('desc', null);
    
        if ($_id == null) {
            return $this->msg(false, '无效的项目编号');
        }
    
        if ($name == null) {
            return $this->msg(false, '请填写项目名称');
        }
    
        if ($sn == null) {
            return $this->msg(false, '请填写项目编号');
        }
    
        if ($desc == null) {
            return $this->msg(false, '请填写项目描述');
        }
    
        $oldProjectInfo = $this->_project->findOne(array(
                '_id' => myMongoId($_id)
        ));
    
        if ($this->checkProjectExist($name) && $oldProjectInfo['name'] != $name) {
            return $this->msg(false, '项目名称已经存在');
        }
    
        if ($this->checkProjectExist($sn) && $oldProjectInfo['sn'] != $sn) {
            return $this->msg(false, '项目编号已经存在');
        }
    
        $project = array();
        $project['name'] = $name;
        $project['sn'] = $sn;
        $project['desc'] = $desc;
        $this->_project->update(array(
                '_id' => myMongoId($_id)
        ), array(
                '$set' => $project
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
            $this->_project->remove(array(
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
    private function checkProjectExist($info)
    {
        $info = $this->_project->findOne(array(
                '$or' => array(
                        array(
                                'name' => $info
                        ),
                        array(
                                'sn' => $info
                        )
                )
        ));
    
        if ($info == null) {
            return false;
        }
        return true;
    }
    
}
