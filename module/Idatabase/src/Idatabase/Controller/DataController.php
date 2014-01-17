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

    /**
     * 读取当前数据集合的mongocollection实例
     *
     * @var object
     */
    private $_data;

    /**
     * 读取数据属性结构的mongocollection实例
     *
     * @var object
     */
    private $_structure;

    /**
     * 读取集合列表集合的mongocollection实例
     *
     * @var object
     */
    private $_collection;

    /**
     * 当前集合所属项目
     *
     * @var string
     */
    private $_project_id = '';

    /**
     * 当前集合所属集合 集合的alias别名或者_id的__toString()结果
     *
     * @var string
     */
    private $_collection_id = '';

    /**
     * 存储数据的物理集合名称
     *
     * @var string
     */
    private $_collection_name = '';

    /**
     * 存储当前集合的结局结构信息
     *
     * @var array
     */
    private $_schema = null;

    /**
     * 存储查询显示字段列表
     *
     * @var array
     */
    private $_fields = array(
        '_id' => true,
        '__CREATE_TIME__' => true,
        '__MODIFY_TIME__' => true
    );

    /**
     * 存储字段与字段名称的数组
     *
     * @var array
     */
    private $_title = array(
        '_id' => '系统编号',
        '__CREATE_TIME__' => '创建时间',
        '__MODIFY_TIME__' => '更新时间'
    );

    /**
     * 存储关联数据的集合数据
     *
     * @var array
     */
    private $_rshData = array();

    /**
     * 排序的mongocollection实例
     *
     * @var string
     */
    private $_order;

    /**
     * 数据集合映射物理集合
     *
     * @var object
     */
    private $_mapping;

    /**
     * 当集合为树状集合时，存储父节点数据的集合名称
     *
     * @var string
     */
    private $_fatherField = '';

    /**
     * 存储当前collection的关系集合数据
     *
     * @var array
     */
    private $_rshCollection = array();

    /**
     * 无法解析的json数组异常时，错误提示信息
     *
     * @var string
     */
    private $_jsonExceptMessage = '子文档类型数据必须符合标准json格式，示例：{"a":1}<br />1.请注意属性务必使用双引号包裹<br />2.请检查Json数据是否完整<br />';

    /**
     * 为了防止死循环
     *
     * @var int
     */
    private $_maxDepth = 1000;

    /**
     * 初始化函数
     *
     * @see \My\Common\ActionController::init()
     */
    public function init()
    {
        resetTimeMemLimit();
        
        // 特殊处理包含点的变量,将__DOT__转换为.
        convertVarNameWithDot($_POST);
        convertVarNameWithDot($_FILES);
        convertVarNameWithDot($_REQUEST);
        
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_collection_id = $this->getCollectionIdByAlias($this->_collection_id);
        $this->_collection_name = 'idatabase_collection_' . $this->_collection_id;
        
        $this->_mapping = $this->model(IDATABASE_MAPPING);
        
        $mapCollection = $this->_mapping->findOne(array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id,
            'active' => true
        ));
        if ($mapCollection != null) {
            $this->_data = $this->model($mapCollection['collection'], $mapCollection['database'], $mapCollection['cluster']);
        } else {
            $this->_data = $this->model($this->_collection_name);
        }
        
        $this->_structure = $this->model(IDATABASE_STRUCTURES);
        
        $this->_schema = $this->getSchema();
        $this->_order = $this->model(IDATABASE_COLLECTION_ORDERBY);
    }

    /**
     * 读取集合内的全部数据
     *
     * @author young
     * @name 读取集合内的全部数据
     * @version 2013.12.23 young
     */
    public function indexAction()
    {
        $rst = array();
        $query = array();
        $sort = array();
        
        $action = $this->params()->fromQuery('action', null);
        $search = $this->params()->fromQuery('search', null);
        $sort = $this->params()->fromQuery('sort', null);
        $start = intval($this->params()->fromQuery('start', 0));
        $limit = intval($this->params()->fromQuery('limit', 10));
        
        if ($action == 'search' || $action == 'excel') {
            $query = $this->searchCondition();
        }
        
        if ($search != null) {
            if (! isset($this->_schema['combobox']['rshCollectionKeyField'])) {
                return $this->msg(false, '关系集合的值');
            }
            $search = preg_replace("/\s/", '', $search);
            $explode = explode(',', $search);
            $query['$and'][] = array(
                $this->_schema['combobox']['rshCollectionKeyField'] => myMongoRegex(end($explode))
            );
        }
        
        $jsonSearch = $this->jsonSearch();
        if ($jsonSearch) {
            $query['$and'][] = $jsonSearch;
        }
        
        if (empty($sort)) {
            $sort = $this->defaultOrder();
        }
        
        $cursor = $this->_data->find($query, $this->_fields);
        $total = $cursor->count();
        $cursor->sort($sort);
        if ($action !== 'excel') {
            $cursor->skip($start)->limit($limit);
        }
        
        $datas = iterator_to_array($cursor, false);
        $datas = $this->comboboxSelectedValues($datas);
        
        if ($action == 'excel') {
            // 在导出数据的情况下，将关联数据显示为关联集合的显示字段数据
            $this->dealRshData();
            // 结束
            convertToPureArray($datas);
            array_walk($datas, function (&$value, $key)
            {
                ksort($value);
                array_walk($value, function (&$cell, $field)
                {
                    if (isset($this->_rshData[$field])) {
                        $cell = $this->_rshData[$field][$cell];
                    }
                });
            });
            
            $excel = array(
                'title' => array_values($this->_title),
                'result' => $datas
            );
            arrayToExcel($excel);
        }
        return $this->rst($datas, $total, true);
    }

    /**
     * 处理数据中的关联数据
     */
    private function dealRshData()
    {
        foreach ($this->_rshCollection as $_id => $detail) {
            $_id = $this->getCollectionIdByAlias($_id);
            $collectionName = 'idatabase_collection_' . $_id;
            $model = $this->model($collectionName);
            $cursor = $model->find(array(), array(
                $detail['rshCollectionKeyField'] => true,
                $detail['rshCollectionValueField'] => true
            ));
            
            $datas = array();
            while ($cursor->hasNext()) {
                $row = $cursor->getNext();
                $datas[$row[$detail['rshCollectionValueField']]] = $row[$detail['rshCollectionKeyField']];
            }
            $this->_rshData[$detail['collectionField']] = $datas;
        }
    }

    /**
     * 处理combobox产生的追加数据
     *
     * @param array $datas            
     * @return array
     */
    private function comboboxSelectedValues($datas)
    {
        $idbComboboxSelectedValue = trim($this->params()->fromQuery('idbComboboxSelectedValue', ''));
        if (! empty($idbComboboxSelectedValue)) {
            $comboboxSelectedLists = explode(',', $idbComboboxSelectedValue);
            if (is_array($comboboxSelectedLists) && ! empty($comboboxSelectedLists) && isset($this->_schema['combobox']['rshCollectionKeyField']) && isset($this->_schema['combobox']['rshCollectionValueField'])) {
                $rshCollectionValueField = $this->_schema['combobox']['rshCollectionValueField'];
                array_walk($comboboxSelectedLists, function (&$value, $index) use($rshCollectionValueField)
                {
                    switch ($this->_schema['post'][$rshCollectionValueField]['type']) {
                        case 'numberfield':
                            $value = preg_match("/^[0-9]+\.[0-9]+$/", $value) ? floatval($value) : intval($value);
                            break;
                        default:
                            break;
                    }
                });
                $cursor = $this->_data->find(array(
                    $rshCollectionValueField => array(
                        '$in' => $rshCollectionValueField == '_id' ? myMongoId($comboboxSelectedLists) : $comboboxSelectedLists
                    )
                ), $this->_fields);
                $extraDatas = iterator_to_array($cursor, false);
                $datas = array_merge($datas, $extraDatas);
                $uniqueArray = array();
                array_walk($datas, function ($value, $key) use(&$datas, &$uniqueArray)
                {
                    if (! in_array($value['_id'], $uniqueArray)) {
                        $uniqueArray[] = $value['_id'];
                    } else {
                        unset($datas[$key]);
                    }
                });
                $datas = array_values($datas);
            }
        }
        return $datas;
    }

    /**
     * 追加json查询条件
     *
     * @return boolean or array
     */
    private function jsonSearch()
    {
        $jsonSearch = trim($this->params()->fromQuery('jsonSearch', ''));
        if (! empty($jsonSearch)) {
            if (isJson($jsonSearch)) {
                try {
                    return Json::decode($jsonSearch, Json::TYPE_ARRAY);
                } catch (\Exception $e) {}
            }
        }
        return false;
    }

    /**
     * 导出excel表格
     *
     * @author young
     * @name 导出excel表格
     * @version 2013.11.19 young
     */
    public function excelAction()
    {
        $forwardPlugin = $this->forward();
        $returnValue = $forwardPlugin->dispatch('idatabase/data/index', array(
            'action' => 'excel'
        ));
        return $returnValue;
    }

    /**
     * 获取树状表格数据
     *
     * 解决方案路径：
     * http://www.sencha.com/forum/showthread.php?152584-EXT-4.0.7-TreeStore-loading-twice-if-autoload-to-false
     */
    public function treeAction()
    {
        if (empty($this->_fatherField)) {
            return $this->msg(false, '树形结构，请设定字段属性和父字段属性');
        }
        
        fb($this->_fatherField, 'LOG');
        $fatherValue = $this->params()->fromQuery('fatherValue', '');
        $tree = $this->tree($this->_fatherField, $fatherValue);
        if (! is_array($tree)) {
            return $tree;
        }
        return new JsonModel($tree);
    }

    /**
     * 递归的方式获取树状数据
     *
     * @param string $fatherField            
     * @param string $fatherValue            
     * @return Ambigous <\Zend\View\Model\JsonModel, multitype:string Ambigous <boolean, bool> >|multitype:|multitype:Ambigous <\MongoId, boolean>
     */
    private function tree($fatherField, $fatherValue = '', $depth = 0)
    {
        $rshCollection = isset($this->_schema['post'][$fatherField]['rshCollection']) ? $this->_schema['post'][$fatherField]['rshCollection'] : '';
        if (empty($rshCollection))
            return $this->msg(false, '无效的关联集合');
        
        if ($this->_schema['post'][$fatherField]['type'] === 'numberfield') {
            $fatherValue = preg_match("/^[0-9]+\.[0-9]+$/", $fatherValue) ? floatval($fatherValue) : intval($fatherValue);
        }
        
        $rshCollectionKeyField = $this->_rshCollection[$rshCollection]['rshCollectionKeyField'];
        $rshCollectionValueField = $this->_rshCollection[$rshCollection]['rshCollectionValueField'];
        
        if ($fatherField == '')
            return $this->msg(false, '$fatherField不存在');
        
        if ($fatherField === '_id')
            $fatherValue = myMongoId($fatherValue);
        
        $cursor = $this->_data->find(array(
            $fatherField => $fatherValue
        ));
        
        if ($cursor->count() == 0)
            return array();
        
        $datas = array();
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            if ($row[$rshCollectionValueField] instanceof \MongoId) {
                $fatherValue = $row[$rshCollectionValueField]->__toString();
            } else {
                $fatherValue = $row[$rshCollectionValueField];
            }
            
            $children = null;
            if ($depth < $this->_maxDepth) {
                $children = $this->tree($fatherField, $fatherValue, $depth ++);
            }
            if (! empty($children)) {
                $row['expanded'] = true;
                $row['children'] = $children;
            } else {
                $row['leaf'] = true;
            }
            $datas[] = $row;
        }
        return $datas;
    }

    /**
     * 添加新数据
     *
     * @author young
     * @name 添加新数据
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function addAction()
    {
        try {
            $datas = array();
            $datas = array_intersect_key($_POST, $this->_schema['post']);
            $files = array_intersect_key($_FILES, $this->_schema['file']);
            
            if (empty($datas) && empty($files))
                return $this->msg(false, '提交数据中未包含有效字段');
            
            if (! empty($files)) {
                foreach ($_FILES as $fieldName => $file) {
                    if ($file['name'] != '') {
                        if ($file['error'] == UPLOAD_ERR_OK) {
                            $fileInfo = $this->_data->storeToGridFS($fieldName);
                            if (isset($fileInfo['_id']) && $fileInfo['_id'] instanceof \MongoId)
                                $datas[$fieldName] = $fileInfo['_id']->__toString();
                            else
                                return $this->msg(false, '文件写入GridFS失败');
                        } else {
                            return $this->msg(false, '文件上传失败,error code:' . $file['error']);
                        }
                    }
                }
            }
            
            try {
                $datas = $this->dealData($datas);
            } catch (\Zend\Json\Exception\RuntimeException $e) {
                return $this->msg(false, $e->getMessage() . $this->_jsonExceptMessage);
            }
            
            if (empty($datas)) {
                return $this->msg(false, '未发现添加任何有效数据');
            }
            $datas = $this->_data->insertByFindAndModify($datas);
            return $this->msg(true, '提交数据成功');
        } catch (\Exception $e) {
            return $this->msg(false, $e->getTraceAsString());
        }
    }

    /**
     * 编辑新的集合信息/关联字段的集合信息/fatherField字段信息
     *
     * @author young
     * @name 编辑新的集合信息
     * @version 2013.11.20 young
     * @return JsonModel
     */
    public function editAction()
    {
        $_id = $this->params()->fromPost('_id', null);
        if ($_id == null) {
            return $this->msg(false, '无效的_id');
        }
        
        $datas = array();
        $datas = array_intersect_key($_POST, $this->_schema['post']);
        $files = array_intersect_key($_FILES, $this->_schema['file']);
        
        if (empty($datas) && empty($files))
            return $this->msg(false, '提交数据中未包含有效字段');
        
        $oldDataInfo = $this->_data->findOne(array(
            '_id' => myMongoId($_id)
        ));
        
        if ($oldDataInfo == null) {
            return $this->msg(false, '提交编辑的数据不存在');
        }
        
        if (! empty($files)) {
            foreach ($_FILES as $fieldName => $file) {
                if ($file['name'] != '') {
                    if ($file['error'] == UPLOAD_ERR_OK) {
                        $this->_data->removeFileFromGridFS($oldDataInfo[$fieldName]);
                        $fileInfo = $this->_data->storeToGridFS($fieldName);
                        if (isset($fileInfo['_id']) && $fileInfo['_id'] instanceof \MongoId)
                            $datas[$fieldName] = $fileInfo['_id']->__toString();
                        else
                            return $this->msg(false, '文件写入GridFS失败');
                    } else {
                        return $this->msg(false, '文件上传失败,error code:' . $file['error']);
                    }
                }
            }
        }
        
        try {
            $datas = $this->dealData($datas);
        } catch (\Zend\Json\Exception\RuntimeException $e) {
            return $this->msg(false, $e->getMessage() . $this->_jsonExceptMessage);
        }
        
        if (empty($datas)) {
            return $this->msg(false, '未发现任何信息变更');
        }
        
        try {
            $this->_data->update(array(
                '_id' => myMongoId($_id)
            ), array(
                '$set' => $datas
            ));
        } catch (\Exception $e) {
            return $this->msg(false, $e->getMessage());
        }
        return $this->msg(true, '编辑信息成功');
    }

    /**
     * 批量更新数据
     *
     * @author young
     * @name 批量更新数据,只更新特定数据，不包含2的坐标和文件字段
     * @version 2013.12.10 young
     * @return JsonModel
     */
    public function saveAction()
    {
        $updateInfos = $this->params()->fromPost('updateInfos', null);
        try {
            $updateInfos = Json::decode($updateInfos, Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return $this->msg(false, '无效的json字符串');
        }
        
        if (! is_array($updateInfos)) {
            return $this->msg(false, '更新数据无效');
        }
        
        foreach ($updateInfos as $row) {
            $_id = $row['_id'];
            unset($row['_id']);
            
            $oldDataInfo = $this->_data->findOne(array(
                '_id' => myMongoId($_id)
            ));
            if ($oldDataInfo != null) {
                $datas = array_intersect_key($row, $this->_schema['post']);
                if (! empty($datas)) {
                    try {
                        $datas = $this->dealData($datas);
                    } catch (\Zend\Json\Exception\RuntimeException $e) {
                        return $this->msg(false, $e->getMessage() . $this->_jsonExceptMessage);
                    }
                    
                    try {
                        $this->_data->update(array(
                            '_id' => myMongoId($_id)
                        ), array(
                            '$set' => $datas
                        ));
                    } catch (\Exception $e) {
                        return $this->msg(false, exceptionMsg($e));
                    }
                }
            }
        }
        
        return $this->msg(true, '更新数据成功');
    }

    /**
     * 删除数据
     *
     * @author young
     * @name 删除数据
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
            $this->_data->remove(array(
                '_id' => myMongoId($row)
            ));
        }
        return $this->msg(true, '删除数据成功');
    }

    /**
     * 清空某个数据结合
     * 注意，为了确保数据安全，需要输入当前用户的登录密码
     */
    public function dropAction()
    {
        $password = $this->params()->fromPost('password', null);
        if ($password == null) {
            return $this->msg(false, '请输入当前用户的登录密码');
        }
        
        if (empty($_SESSION['account']['password'])) {
            return $this->msg(false, '当前会话已经过期，请重新登录');
        }
        
        if ($_SESSION['account']['password'] !== sha1($password)) {
            return $this->msg(false, '您输入的登录密码错误，请重新输入');
        }
        
        $rst = $this->_data->drop();
        if ($rst['ok'] == 1) {
            return $this->msg(true, '清空数据成功');
        } else {
            fb($rst, \FirePHP::LOG);
            return $this->msg(false, '清空数据失败' . Json::encode($rst));
        }
    }

    /**
     * 获取集合的数据结构
     *
     * @return array
     */
    private function getSchema()
    {
        $schema = array(
            'file' => array(),
            'post' => array(),
            'all' => array(),
            'combobox' => array(
                'rshCollectionValueField' => '_id'
            )
        );
        
        $cursor = $this->_structure->find(array(
            'collection_id' => $this->_collection_id
        ));
        $cursor->sort(array(
            'orderBy' => 1,
            '_id' => - 1
        ));
        
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            
            $type = $row['type'] == 'filefield' ? 'file' : 'post';
            $schema[$type][$row['field']] = $row;
            $schema['all'][$row['field']] = $row;
            $this->_fields[$row['field']] = true;
            $this->_title[$row['field']] = $row['label'];
            
            if ($row['rshKey']) {
                $schema['combobox']['rshCollectionKeyField'] = $row['field'];
            }
            
            if ($row['rshValue']) {
                $schema['combobox']['rshCollectionValueField'] = $row['field'];
            }
            
            if (isset($row['isFatherField']) && $row['isFatherField']) {
                $this->_fatherField = $row['field'];
            }
            
            if (! empty($row['rshCollection'])) {
                $rshCollectionStructures = $this->_structure->findAll(array(
                    'collection_id' => $this->getCollectionIdByAlias($row['rshCollection'])
                ));
                if (! empty($rshCollectionStructures)) {
                    $rshCollectionKeyField = '';
                    $rshCollectionValueField = '_id';
                    foreach ($rshCollectionStructures as $rshCollectionStructure) {
                        if ($rshCollectionStructure['rshKey'])
                            $rshCollectionKeyField = $rshCollectionStructure['field'];
                        
                        if ($rshCollectionStructure['rshValue'])
                            $rshCollectionValueField = $rshCollectionStructure['field'];
                    }
                    
                    if (empty($rshCollectionKeyField))
                        throw new \Exception('关系集合未设定关系键值');
                    
                    $this->_rshCollection[$row['rshCollection']] = array(
                        'collectionField' => $row['field'],
                        'rshCollectionKeyField' => $rshCollectionKeyField,
                        'rshCollectionValueField' => $rshCollectionValueField
                    );
                } else {
                    throw new \Exception('关系集合属性尚未设定');
                }
            }
        }
        
        ksort($this->_title);
        $this->_schema = $schema;
        return $schema;
    }

    /**
     * 处理入库的数据
     *
     * @param array $datas            
     * @return array
     */
    private function dealData($datas)
    {
        $validPostData = array_intersect_key($datas, $this->_schema['post']);
        array_walk($validPostData, function (&$value, $key)
        {
            if (! empty($this->_schema['post'][$key]['filter'])) {
                $value = filter_var($value, $this->_schema['post'][$key]['filter']);
            }
            
            $value = formatData($value,$this->_schema['post'][$key]['type']);
            
        });
        
        $validFileData = array_intersect_key($datas, $this->_schema['file']);
        $validData = array_merge($validPostData, $validFileData);
        return $validData;
    }

    /**
     * 处理检索条件
     */
    private function searchCondition()
    {
        $query = array();
        
        // 扩展两个系统默认参数加入查询条件
        $this->_schema['post'] = array_merge($this->_schema['post'], array(
            '__CREATE_TIME__' => array(
                'type' => 'datefield'
            ),
            '__MODIFY_TIME__' => array(
                'type' => 'datefield'
            )
        ));
        
        foreach ($this->_schema['post'] as $field => $detail) {
            $subQuery = array();
            $not = false;
            $exact = false;
            
            if (isset($_REQUEST['exclusive__' . $field]) && filter_var($_REQUEST['exclusive__' . $field], FILTER_VALIDATE_BOOLEAN))
                $not = true;
            
            if (isset($_REQUEST['exactMatch__' . $field]) && filter_var($_REQUEST['exactMatch__' . $field], FILTER_VALIDATE_BOOLEAN))
                $exact = true;
            
            if (! empty($detail['rshCollection'])) {
                $exact = true;
            }
            
            if (isset($_REQUEST[$field])) {
                if (is_array($_REQUEST[$field]) && trim(join('', $_REQUEST[$field])) == '')
                    continue;
                
                if (! is_array($_REQUEST[$field]) && trim($_REQUEST[$field]) == '')
                    continue;
                
                switch ($detail['type']) {
                    case 'numberfield':
                        if (is_array($_REQUEST[$field])) {
                            $min = trim($_REQUEST[$field]['min']);
                            $max = trim($_REQUEST[$field]['max']);
                            $min = preg_match("/^[0-9]+\.[0-9]+$/", $min) ? floatval($min) : intval($min);
                            $max = preg_match("/^[0-9]+\.[0-9]+$/", $max) ? floatval($max) : intval($max);
                            
                            if ($min === $max) {
                                if ($not) {
                                    $subQuery[$field]['$ne'] = $min;
                                } else {
                                    $subQuery[$field] = $min;
                                }
                            } else {
                                if ($not) {
                                    if (! empty($min))
                                        $subQuery['$or'][][$field]['$lte'] = $min;
                                    if (! empty($max))
                                        $subQuery['$or'][][$field]['$gte'] = $max;
                                } else {
                                    if (! empty($min))
                                        $subQuery[$field]['$gte'] = $min;
                                    if (! empty($max))
                                        $subQuery[$field]['$lte'] = $max;
                                }
                            }
                        } else {
                            $value = preg_match("/^[0-9]+\.[0-9]+$/", $_REQUEST[$field]) ? floatval($_REQUEST[$field]) : intval($_REQUEST[$field]);
                            if ($not) {
                                $subQuery[$field]['$ne'] = $value;
                            } else {
                                $subQuery[$field] = $value;
                            }
                        }
                        break;
                    case 'datefield':
                        $start = trim($_REQUEST[$field]['start']);
                        $end = trim($_REQUEST[$field]['end']);
                        $start = preg_match("/^[0-9]+$/", $start) ? new \MongoDate(intval($start)) : new \MongoDate(strtotime($start));
                        $end = preg_match("/^[0-9]+$/", $end) ? new \MongoDate(intval($end)) : new \MongoDate(strtotime($end));
                        if ($not) {
                            if (! empty($start))
                                $subQuery['$or'][][$field]['$lte'] = $start;
                            if (! empty($end))
                                $subQuery['$or'][][$field]['$gte'] = $end;
                        } else {
                            if (! empty($start))
                                $subQuery[$field]['$gte'] = $start;
                            if (! empty($end))
                                $subQuery[$field]['$lte'] = $end;
                        }
                        break;
                    case '2dfield':
                        $lng = floatval(trim($_REQUEST[$field]['lng']));
                        $lat = floatval(trim($_REQUEST[$field]['lat']));
                        $distance = ! empty($_REQUEST[$field]['distance']) ? floatval($_REQUEST[$field]['distance']) : 10;
                        $subQuery = array(
                            '$near' => array(
                                $lng,
                                $lat
                            ),
                            '$maxDistance' => $distance / 111.12
                        );
                        break;
                    case 'boolfield':
                        $subQuery[$field] = filter_var(trim($_REQUEST[$field]), FILTER_VALIDATE_BOOLEAN);
                        break;
                    default:
                        if ($not)
                            $subQuery[$field]['$ne'] = trim($_REQUEST[$field]);
                        else
                            $subQuery[$field] = $exact ? trim($_REQUEST[$field]) : myMongoRegex($_REQUEST[$field]);
                        break;
                }
                $query['$and'][] = $subQuery;
            }
        }
        
        if (empty($query['$and'])) {
            return array();
        }
        
        return $query;
    }

    /**
     * 根据条件创建排序条件
     *
     * @return array
     */
    private function sortCondition()
    {
        $sort = $this->defaultOrder();
        return $sort;
    }

    /**
     * 获取当前集合的排列顺序
     *
     * @return array
     */
    private function defaultOrder()
    {
        $cursor = $this->_order->find(array(
            'collection_id' => $this->_collection_id
        ));
        $cursor->sort(array(
            'priority' => - 1,
            '_id' => - 1
        ));
        
        $order = array();
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $order[$row['field']] = $row['order'];
        }
        
        if (! isset($order['_id'])) {
            $order['_id'] = - 1;
        }
        return $order;
    }

    /**
     * 根据集合的名称获取集合的_id
     *
     * @param string $alias            
     * @throws \Exception or string
     */
    private function getCollectionIdByAlias($alias)
    {
        try {
            new \MongoId($alias);
            return $alias;
        } catch (\MongoException $ex) {}
        
        $collectionInfo = $this->_collection->findOne(array(
            'project_id' => $this->_project_id,
            'alias' => $alias
        ));
        
        if ($collectionInfo == null) {
            throw new \Exception('集合名称不存在于指定项目');
        }
        
        return $collectionInfo['_id']->__toString();
    }

    /**
     * 根据集合的编号获取集合的别名
     *
     * @param string $_id            
     * @throws \Exception
     */
    private function getCollectionAliasById($_id)
    {
        if (! ($_id instanceof \MongoId)) {
            $_id = myMongoId($_id);
        }
        $collectionInfo = $this->_collection->findOne(array(
            'project_id' => $this->_project_id,
            '_id' => $_id
        ));
        if ($collectionInfo == null) {
            throw new \Exception('集合名称不存在于指定项目');
        }
        
        return $collectionInfo['alias'];
    }

    /**
     * 对于集合进行了任何操作，那么出发联动事件，联动修改其他集合的相关数据
     * 提交全部POST参数以及系统默认的触发参数__TRIGER__
     * $_POST['__TRIGER__']['collection'] 触发事件集合的名称
     * $_POST['__TRIGER__']['controller'] 触发控制器
     * $_POST['__TRIGER__']['action'] 触发动作
     * 为了确保调用安全，签名方法为所有POST参数按照字母顺序排列，构建的字符串substr(sha1(k1=v1&k2=v2连接密钥),0,32)，做个小欺骗，让签名看起来很像MD5的。
     */
    public function __destruct()
    {
        fastcgi_finish_request();
        $controller = $this->params('controller');
        $action = $this->params('action');
        $_POST['__TRIGER__'] = array(
            'collection' => $this->getCollectionAliasById($this->_collection_id),
            'controller' => $controller,
            'action' => $action
        );
        $collectionInfo = $this->_collection->findOne(array(
            '_id' => myMongoId($this->_collection_id),
            'isAutoHook' => true
        ));
        
        if ($collectionInfo !== null && isset($collectionInfo['hook']) && filter_var($collectionInfo['hook'], FILTER_VALIDATE_URL) !== false) {
            $sign = dataSignAlgorithm($_POST, $collectionInfo['hookKey']);
            $_POST['__SIGN__'] = $sign;
            doPost($collectionInfo['hook'], $_POST);
        }
        return false;
    }

    
}
