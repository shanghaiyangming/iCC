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
use Zend\Json\Json;

class CollectionController extends BaseActionController
{

    private $_collection;

    private $_plugin_collection;

    private $_project_id;

    private $_lock;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        $this->_plugin_collection = $this->model(IDATABASE_PLUGINS_COLLECTIONS);
        $this->_lock = $this->model(IDATABASE_LOCK);
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
        $search = trim($this->params()->fromQuery('query', ''));
        $plugin_id = $this->params()->fromQuery('plugin_id', '');
        
        $sort = array(
            'orderBy' => 1,
            '_id' => - 1
        );
        
        $query = array(
            'plugin_id' => $plugin_id,
            'project_id' => $this->_project_id
        );
        
        if ($search != '') {
            $search = myMongoRegex($search);
            $query = array(
                '$and' => array(
                    $query,
                    array(
                        '$or' => array(
                            array(
                                'name' => $search
                            ),
                            array(
                                'alias' => $search
                            )
                        )
                    )
                )
            );
        }
        
        $datas = array();
        $cursor = $this->_collection->find($query);
        $cursor->sort($sort);
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $row['locked'] = false;
            $lockInfo = $this->_lock->count(array(
                'project_id' => $this->_project_id,
                'collection_id' => myMongoId($row['_id']),
                'active' => true
            ));
            if ($lockInfo > 0) {
                $row['locked'] = true;
            }
            $datas[] = $row;
        }
        return $this->rst($datas, $cursor->count(), true);
    }
    
    private function lock() {
        
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
        // $plugin = new \Idatabase\Model\Plugin();
        try {
            $project_id = $this->_project_id;
            $name = $this->params()->fromPost('name', null);
            $alias = $this->params()->fromPost('alias', null);
            $type = $this->params()->fromPost('type', null);
            $isTree = filter_var($this->params()->fromPost('isTree', false), FILTER_VALIDATE_BOOLEAN);
            $desc = $this->params()->fromPost('desc', null);
            $orderBy = $this->params()->fromPost('orderBy', 0);
            $isRowExpander = filter_var($this->params()->fromPost('isRowExpander', false), FILTER_VALIDATE_BOOLEAN);
            $rowExpanderTpl = $this->params()->fromPost('rowExpanderTpl', '');
            $plugin = filter_var($this->params()->fromPost('plugin', false), FILTER_VALIDATE_BOOLEAN);
            $plugin_id = $this->params()->fromPost('plugin_id', '');
            
            if ($project_id == null) {
                return $this->msg(false, '无效的项目编号');
            }
            
            if ($name == null) {
                return $this->msg(false, '请填写集合名称');
            }
            
            if ($alias == null || ! preg_match("/[a-z0-9_]/i", $alias)) {
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
            
            if ($this->checkPluginNameExist($name) || $this->checkPluginAliasExist($alias)) {
                return $this->msg(false, '集合名称或者别名在插件命名中已经存在，请勿重复使用');
            }
            
            if ($this->checkCollecionNameExist($name) || $this->checkCollecionAliasExist($alias)) {
                return $this->msg(false, '集合名称或者别名已经被使用，请勿重复使用');
            }
            
            $datas = array();
            $datas['project_id'] = array(
                $project_id
            );
            $datas['name'] = $name;
            $datas['alias'] = $alias;
            $datas['type'] = $type;
            $datas['isTree'] = $isTree;
            $datas['desc'] = $desc;
            $datas['orderBy'] = $orderBy;
            $datas['plugin'] = $plugin;
            $datas['plugin_id'] = $plugin_id;
            $datas['plugin_collection_id'] = $this->addPluginCollection($datas);
            $datas['isRowExpander'] = $isRowExpander;
            $datas['rowExpanderTpl'] = $rowExpanderTpl;
            $this->_collection->insert($datas);
            
            return $this->msg(true, '添加集合成功');
        } catch (\Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }

    /**
     * 批量编辑集合信息
     *
     * @author young
     * @name 批量编辑集合信息
     * @version 2013.12.02 young
     * @return JsonModel
     */
    public function saveAction()
    {
        return $this->msg(true, '集合编辑不支持批量修改功能');
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
        $isTree = filter_var($this->params()->fromPost('isTree', false), FILTER_VALIDATE_BOOLEAN);
        $desc = $this->params()->fromPost('desc', null);
        $orderBy = $this->params()->fromPost('orderBy', 0);
        $isRowExpander = filter_var($this->params()->fromPost('isRowExpander', false), FILTER_VALIDATE_BOOLEAN);
        $rowExpanderTpl = $this->params()->fromPost('rowExpanderTpl', '');
        $plugin = filter_var($this->params()->fromPost('plugin', false), FILTER_VALIDATE_BOOLEAN);
        $plugin_id = $this->params()->fromPost('plugin_id', '');
        
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
        
        $oldCollectionInfo = $this->_collection->findOne(array(
            '_id' => myMongoId($_id)
        ));
        
        if ($this->checkCollecionNameExist($name) && $oldCollectionInfo['name'] != $name) {
            return $this->msg(false, '集合名称已经存在');
        }
        
        if ($this->checkCollecionAliasExist($alias) && $oldCollectionInfo['alias'] != $alias) {
            return $this->msg(false, '集合别名已经存在');
        }
        
        if (($this->checkPluginNameExist($name) && $oldCollectionInfo['name'] != $name) || ($this->checkPluginAliasExist($alias) && $oldCollectionInfo['alias'] != $alias)) {
            return $this->msg(false, '集合名称或者别名在插件命名中已经存在，请勿重复使用');
        }
        
        $datas = array();
        $datas['project_id'] = array(
            $project_id
        );
        $datas['name'] = $name;
        $datas['alias'] = $alias;
        $datas['type'] = $type;
        $datas['isTree'] = $isTree;
        $datas['desc'] = $desc;
        $datas['orderBy'] = $orderBy;
        $datas['plugin'] = $plugin;
        $datas['plugin_id'] = $plugin_id;
        $datas['plugin_collection_id'] = $this->editPluginCollection($datas);
        $datas['isRowExpander'] = $isRowExpander;
        $datas['rowExpanderTpl'] = $rowExpanderTpl;
        
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
            $this->_collection->update(array(
                '_id' => myMongoId($row)
            ), array(
                'project_id' => $this->_project_id
            ));
        }
        return $this->msg(true, '删除信息成功');
    }

    public function dropAction()
    {}

    /**
     * 检测一个集合是否存在，根据名称和编号
     *
     * @param string $info            
     * @return boolean
     */
    private function checkCollecionNameExist($info)
    {
        // 检查当前项目集合中是否包含这些命名
        $info = $this->_collection->findOne(array(
            'name' => $info,
            'project_id' => $this->_project_id
        ));
        if ($info == null) {
            return false;
        }
        return true;
    }

    private function checkCollecionAliasExist($info)
    {
        // 检查当前项目集合中是否包含这些命名
        $info = $this->_collection->findOne(array(
            'alias' => $info,
            'project_id' => $this->_project_id
        ));
        if ($info == null) {
            return false;
        }
        return true;
    }

    private function checkPluginNameExist($info)
    {
        // 检查插件集合中是否包含这些名称信息
        $info = $this->_collection->findOne(array(
            'name' => $info,
            'plugin' => true
        ));
        if ($info == null) {
            return false;
        }
        return true;
    }

    private function checkPluginAliasExist($info)
    {
        // 检查插件集合中是否包含这些名称信息
        $info = $this->_collection->findOne(array(
            'alias' => $info,
            'plugin' => true
        ));
        if ($info == null) {
            return false;
        }
        return true;
    }

    /**
     * 添加集合到插件集合管理
     *
     * @param array $datas            
     * @return string
     */
    private function addPluginCollection($datas)
    {
        if (empty($datas['plugin_id']))
            return '';
        
        unset($datas['project_id']);
        $this->_plugin_collection->insertRef($datas);
        if ($datas['_id'] instanceof \MongoId)
            return $datas['_id']->__toString();
        
        return '';
    }

    /**
     * 添加集合到插件集合管理
     *
     * @param array $datas            
     * @return string
     */
    private function editPluginCollection($datas)
    {
        $plugin_collection_id = isset($datas['plugin_collection_id']) ? $datas['plugin_collection_id'] : '';
        if (empty($plugin_collection_id)) {
            $this->_plugin_collection->update(array(
                '_id' => myMongoId($plugin_collection_id)
            ), array(
                '$set' => $datas
            ));
        }
        return $plugin_collection_id;
    }
}
