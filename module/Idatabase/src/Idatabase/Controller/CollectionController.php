<?php
/**
 * iDatabase项目内数据集合管理
 *
 * @author young 
 * @version 2013.11.19
 * 
 */
namespace Idatabase\Controller;

use My\Common\ActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;
use Zend\View\Model\JsonModel;

class CollectionController extends BaseActionController
{

    private $_collection;
    private $_project_id;

    public function init()
    {
        $this->_project_id = $this->params()->fromQuery('project_id',null);
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        //注意这里应该增加检查，该项目id是否符合用户操作的权限范围
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
        $query = array('project_id'=>$this->_project_id);
        return $this->findAll(IDATABASE_COLLECTIONS, $query);
    }

    /**
     * 添加新的项目
     *
     * @author young
     * @name 添加新的项目
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function addAction()
    {
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
        $_id = $this->params()->fromPost('_id', array());
        if (empty($_id)) {
            return $this->msg(false, '无效的项目编号');
        }
        
        foreach ($_id as $row) {
            $this->_project->remove(array(
                '_id' => myMongoId($row)
            ));
        }
        return $this->msg(true, '删除信息成功');
    }

    /**
     * 权限分享，账户所属人员如果具备分享权限，可以将项目分享给别的用户
     */
    public function shareAction()
    {}

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
