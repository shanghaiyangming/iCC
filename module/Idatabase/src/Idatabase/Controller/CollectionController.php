<?php
/**
 * iDatabase项目内数据集合管理
 *
 * @author young 
 * @version 2013.11.19
 * 
 */
namespace Idatabase\Controller;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use My\Common\Controller\Action;

class CollectionController extends Action
{

    private $_collection;

    private $_structure;

    private $_plugin;

    private $_project_plugin;

    private $_plugin_collection;

    private $_plugin_structure;

    private $_lock;

    private $_mapping;

    private $_project_id;

    private $_plugin_id = '';

    private $_sync;

    public function init()
    {
        $this->_project_id = isset($_REQUEST['__PROJECT_ID__']) ? trim($_REQUEST['__PROJECT_ID__']) : '';
        $this->_plugin_id = isset($_REQUEST['__PLUGIN_ID__']) ? trim($_REQUEST['__PLUGIN_ID__']) : '';
        $this->_sync = isset($_REQUEST['__SYNC__']) ? filter_var($_REQUEST['__SYNC__'], FILTER_VALIDATE_BOOLEAN) : false;
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model('Idatabase\Model\Collection');
        $this->_structure = $this->model('Idatabase\Model\Structure');
        $this->_project_plugin = $this->model('Idatabase\Model\ProjectPlugin');
        $this->_plugin = $this->model('Idatabase\Model\Plugin');
        $this->_plugin_collection = $this->model('Idatabase\Model\PluginCollection');
        $this->_plugin_structure = $this->model('Idatabase\Model\PluginStructure');
        $this->_lock = $this->model('Idatabase\Model\Lock');
        $this->_mapping = $this->model('Idatabase\Model\Mapping');
    }

    /**
     * 读取指定项目内的全部集合列表
     * 支持专家模式和普通模式显示，对于一些说明表和关系表，请在定义时，定义为普通模式
     *
     * @author young
     * @name 读取指定项目内的全部集合列表
     * @version 2014.01.21 young
     */
    public function indexAction()
    {
        $search = trim($this->params()->fromQuery('query', ''));
        $action = trim($this->params()->fromQuery('action', ''));
        $plugin_id = $this->_plugin_id;
        $sort = array(
            'orderBy' => 1,
            '_id' => - 1
        );
        
        $query = array();
        if ($action === 'all') {
            $query['$and'][] = array(
                'project_id' => $this->_project_id
            );
        } else {
            if (empty($plugin_id)) {
                $query['$and'][] = array(
                    'project_id' => $this->_project_id
                );
            }
            $query['$and'][] = array(
                'plugin_id' => $plugin_id
            );
        }
        
        if ($search != '') {
            $search = myMongoRegex($search);
            $query['$and'][] = array(
                '$or' => array(
                    array(
                        'name' => $search
                    ),
                    array(
                        'alias' => $search
                    )
                )
            );
        }
        
        $isProfessional = isset($_SESSION['account']['isProfessional']) ? $_SESSION['account']['isProfessional'] : false;
        if ($isProfessional === false) {
            $query['$and'][] = array(
                'isProfessional' => false
            );
        }
        
        if (! $_SESSION['acl']['admin']) {
            $query['$and'][] = array(
                '_id' => array(
                    '$in' => myMongoId($_SESSION['acl']['collection'])
                )
            );
        }
        
        if (empty($plugin_id) || $action === 'all') {
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
        } else {
            $datas = array();
            $cursor = $this->_plugin_collection->find($query);
            $cursor->sort($sort);
            while ($cursor->hasNext()) {
                $row = $cursor->getNext();
                $row['plugin_collection_id'] = myMongoId($row['_id']);
                
                $collectionInfo = $this->_plugin_collection->syncPluginCollection($this->_project_id, $this->_plugin_id, $row['alias']);
                if ($collectionInfo === false) {
                    fb($collectionInfo, 'LOG');
                    fb($row['alias'], 'LOG');
                    throw new \Exception('插件集合中不存在此集合');
                }
                $row['_id'] = $collectionInfo['_id'];
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
            $isProfessional = filter_var($this->params()->fromPost('isProfessional', false), FILTER_VALIDATE_BOOLEAN);
            $isTree = filter_var($this->params()->fromPost('isTree', false), FILTER_VALIDATE_BOOLEAN);
            $desc = $this->params()->fromPost('desc', null);
            $orderBy = $this->params()->fromPost('orderBy', 0);
            $isRowExpander = filter_var($this->params()->fromPost('isRowExpander', false), FILTER_VALIDATE_BOOLEAN);
            $rowExpanderTpl = $this->params()->fromPost('rowExpanderTpl', '');
            $plugin = filter_var($this->params()->fromPost('plugin', false), FILTER_VALIDATE_BOOLEAN);
            $plugin_id = $this->_plugin_id;
            $isAutoHook = filter_var($this->params()->fromPost('isAutoHook', false), FILTER_VALIDATE_BOOLEAN);
            $hook = trim($this->params()->fromPost('hook', ''));
            $hookKey = trim($this->params()->fromPost('hookKey', ''));
            
            if ($project_id == null) {
                return $this->msg(false, '无效的项目编号');
            }
            
            if ($name == null) {
                return $this->msg(false, '请填写集合名称');
            }
            
            if ($alias == null || ! preg_match("/[a-z0-9_]/i", $alias)) {
                return $this->msg(false, '请填写集合别名，只接受英文与字母');
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
            $datas['isProfessional'] = $isProfessional;
            $datas['isTree'] = $isTree;
            $datas['desc'] = $desc;
            $datas['orderBy'] = $orderBy;
            $datas['plugin'] = $plugin;
            $datas['plugin_id'] = $plugin_id;
            $datas['isRowExpander'] = $isRowExpander;
            $datas['rowExpanderTpl'] = $rowExpanderTpl;
            $datas['isAutoHook'] = $isAutoHook;
            $datas['hook'] = $hook;
            $datas['hookKey'] = $hookKey;
            $datas['plugin_collection_id'] = $this->_plugin_collection->addPluginCollection($datas);
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
        $isProfessional = filter_var($this->params()->fromPost('isProfessional', false), FILTER_VALIDATE_BOOLEAN);
        $isTree = filter_var($this->params()->fromPost('isTree', false), FILTER_VALIDATE_BOOLEAN);
        $desc = $this->params()->fromPost('desc', null);
        $orderBy = $this->params()->fromPost('orderBy', 0);
        $isRowExpander = filter_var($this->params()->fromPost('isRowExpander', false), FILTER_VALIDATE_BOOLEAN);
        $rowExpanderTpl = $this->params()->fromPost('rowExpanderTpl', '');
        $plugin = filter_var($this->params()->fromPost('plugin', false), FILTER_VALIDATE_BOOLEAN);
        $plugin_id = $this->_plugin_id;
        $isAutoHook = filter_var($this->params()->fromPost('isAutoHook', false), FILTER_VALIDATE_BOOLEAN);
        $hook = trim($this->params()->fromPost('hook', ''));
        $hookKey = trim($this->params()->fromPost('hookKey', ''));
        
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
        $datas['isProfessional'] = $isProfessional;
        $datas['isTree'] = $isTree;
        $datas['desc'] = $desc;
        $datas['orderBy'] = $orderBy;
        $datas['plugin'] = $plugin;
        $datas['plugin_id'] = $plugin_id;
        $datas['isRowExpander'] = $isRowExpander;
        $datas['rowExpanderTpl'] = $rowExpanderTpl;
        $datas['isAutoHook'] = $isAutoHook;
        $datas['hook'] = $hook;
        $datas['hookKey'] = $hookKey;
        $datas['plugin_collection_id'] = $this->_plugin_collection->editPluginCollection($datas);
        
        $this->_collection->update(array(
            '_id' => myMongoId($_id)
        ), array(
            '$set' => $datas
        ));
        
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 删除集合
     *
     * @author young
     * @name 删除集合
     * @version 2013.11.14 young
     * @return JsonModel
     */
    public function removeAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        $plugin_id = $this->_plugin_id;
        
        try {
            $_id = Json::decode($_id, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return $this->msg(false, '无效的json字符串');
        }
        
        if (! is_array($_id)) {
            return $this->msg(false, '请选择你要删除的项');
        }
        foreach ($_id as $row) {
            $rowInfo = $this->_collection->findOne(array(
                '_id' => myMongoId($row)
            ));
            
            if ($rowInfo != null) {
                $this->_plugin_collection->removePluginCollection($this->_project_id, $this->_plugin_id, $rowInfo['alias']);
                $this->_collection->remove(array(
                    '_id' => myMongoId($row)
                ));
            }
        }
        return $this->msg(true, '删除信息成功');
    }

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
}
