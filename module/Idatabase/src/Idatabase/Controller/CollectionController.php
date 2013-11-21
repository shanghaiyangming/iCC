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
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if(empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
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
            'project_id' => $this->_project_id
        );
        return $this->findAll(IDATABASE_COLLECTIONS, $query);
    }

    /**
     * 添加新的集合
     *
     * @author young
     * @name 添加新的集合
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function addAction()
    {
        try {
        $project_id = $this->_project_id;
        $name = $this->params()->fromPost('name', null);
        $alias = $this->params()->fromPost('alias', null);
        $type = $this->params()->fromPost('type', null);
        $desc = $this->params()->fromPost('desc', null);
        
        if ($project_id == null) {
            return $this->msg(false, '无效的项目编号');
        }
        
        if ($name == null) {
            return $this->msg(false, '请填写集合名称');
        }
        
        if ($alias == null || ! preg_match("/[a-z0-9]/i", $alias)) {
            return $this->msg(false, '请填写集合别名，只接受英文与字母');
        }
        
        if (! in_array($type, array(
            'common',
            'professional'
        ))) {
            return $this->msg(false, '无效的结合类型');
        }
        
        if ($desc == null) {
            return $this->msg(false, '请填写集合描述');
        }
        
        if ($this->checkCollecionExist($name)) {
            return $this->msg(false, '集合名称已经存在');
        }
        
        if ($this->checkCollecionExist($alias)) {
            return $this->msg(false, '集合别名已经存在');
        }
        
        $datas = array();
        $datas['project_id'] = $project_id;
        $datas['name'] = $name;
        $datas['alias'] = $alias;
        $datas['desc'] = $desc;
        $this->_collection->insert($datas);
        
        return $this->msg(true, '添加信息成功');
        }
        catch(\Exception $e) {
            var_dump($e->getTraceAsString());
        }
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
        $_id = $this->params()->fromPost('_id', null);
        $project_id = $this->_project_id;
        $name = $this->params()->fromPost('name', null);
        $alias = $this->params()->fromPost('alias', null);
        $type = $this->params()->fromPost('type', null);
        $desc = $this->params()->fromPost('desc', null);
        
        if ($_id == null) {
            return $this->msg(false, '无效的集合编号');
        }
        
        if ($project_id == null) {
            return $this->msg(false, '无效的项目编号');
        }
        
        if ($name == null) {
            return $this->msg(false, '请填写集合名称');
        }
        
        if ($alias == null || ! preg_match("/[a-z0-9]/i", $alias)) {
            return $this->msg(false, '请填写集合别名，只接受英文与字母');
        }
        
        if (! in_array($type, array(
            'common',
            'professional'
        ))) {
            return $this->msg(false, '无效的结合类型');
        }
        
        if ($desc == null) {
            return $this->msg(false, '请填写集合描述');
        }
        
        if ($this->checkCollecionExist($name)) {
            return $this->msg(false, '集合名称已经存在');
        }
        
        if ($this->checkCollecionExist($alias)) {
            return $this->msg(false, '集合别名已经存在');
        }
        
        $datas = array();
        $datas['project_id'] = $project_id;
        $datas['name'] = $name;
        $datas['alias'] = $alias;
        $datas['desc'] = $desc;
        
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
                'project_id'=>$this->_project_id
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
                array('project_id'=>$this->_project_id)
            )
        ));
        
        if ($info == null) {
            return false;
        }
        return true;
    }
}
