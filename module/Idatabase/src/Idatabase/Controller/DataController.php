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

    private $_collection;

    private $_project_id;

    private $_collection_id;

    private $_collection_name;

    private $_schema;

    private $_order;

    private $_mapping;

    private $_fatherField = '';

    /**
     * 存储当前collection的关系集合数据
     *
     * @var array
     */
    private $_rshCollection = array();

    /**
     * 初始化函数
     *
     * @see \My\Common\ActionController::init()
     */
    public function init()
    {
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id)) {
            throw new \Exception('$this->_project_id值未设定');
        }
        
        // 处理集合数据开始
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id)) {
            throw new \Exception('$this->_collection_id值未设定');
        }
        $this->_collection_id = $this->getCollectionIdByName($this->_collection_id);
        $this->_collection_name = 'idatabase_collection_' . $this->_collection_id;
        // 处理集合数据结束
        
        $this->_data = $this->model($this->_collection_name);
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
        
        if ($action == 'search' || $action == 'excel') {
            $query = $this->searchCondition();
        }
        
        if (empty($sort)) {
            $sort = $this->defaultOrder();
        }
        
        $cursor = $this->_data->find($query);
        $cursor->sort($sort);
        $rst = iterator_to_array($cursor, false);
        if ($action == 'excel') {
            arrayToExcel($name, convertToPureArray($rst));
        }
        return $this->rst($rst, $cursor->count(), true);
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
        
        $fatherValue = $this->params()->fromQuery('fatherValue', '');
        $tree = $this->tree($this->_fatherField, $fatherValue);
        return new JsonModel($tree);
    }

    /**
     * 递归的方式获取树状数据
     *
     * @param string $fatherNode            
     */
    private function tree($fatherField, $fatherValue = '')
    {
        $rshCollection = isset($this->_schema['post'][$fatherField]['rshCollection']) ? $this->_schema['post'][$fatherField]['rshCollection'] : '';
        if (empty($rshCollection))
            return $this->msg(false, '无效的关联集合');
        
        $rshCollectionKeyField = $this->_rshCollection[$rshCollection]['rshCollectionKeyField'];
        $rshCollectionValueField = $this->_rshCollection[$rshCollection]['rshCollectionValueField'];
        
        if ($fatherField == '')
            return $this->msg(false, '$fatherField不存在');
        
        if ($fatherField == '_id')
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
            $children = $this->tree($fatherField, $fatherValue);
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
            
            $datas = $this->dealData($datas);
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
        
        $datas = $this->dealData($datas);
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
            return $this->msg(false, $e->getTraceAsString());
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
                    $datas = $this->dealData($datas);
                    $this->_data->update(array(
                        '_id' => myMongoId($_id)
                    ), array(
                        '$set' => $datas
                    ));
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
     * 获取集合的数据结构
     *
     * @return array
     */
    private function getSchema()
    {
        $schema = array(
            'file' => array(),
            'post' => array(),
            'all' => array()
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
            
            if (isset($row['isFatherField']) && $row['isFatherField']) {
                $this->_fatherField = $row['field'];
            }
            
            if (! empty($row['rshCollection'])) {
                $row['rshCollection'] = $this->getCollectionIdByName($row['rshCollection']);
                
                $rshCollectionStructures = $this->_structure->findAll(array(
                    'collection_id' => $row['rshCollection']
                ));
                if (! empty($rshCollectionStructures)) {
                    $rshCollectionKeyField = '';
                    $rshCollectionValueField = '_id';
                    foreach ($rshCollectionStructures as $rshCollectionStructure) {
                        if ($rshCollectionStructure['rshKey']) {
                            $rshCollectionKeyField = $rshCollectionStructure['field'];
                        }
                        
                        if ($rshCollectionStructure['rshValue']) {
                            $rshCollectionValueField = $rshCollectionStructure['field'];
                        }
                    }
                    
                    if (empty($rshCollectionKeyField)) {
                        throw new \Exception('关系集合未设定关系键值');
                    }
                    
                    $this->_rshCollection[$row['rshCollection']] = array(
                        'rshCollectionKeyField' => $rshCollectionValueField,
                        'rshCollectionValueField' => $rshCollectionValueField
                    );
                } else {
                    throw new \Exception('关系集合属性尚未设定');
                }
            }
        }
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
            switch ($this->_schema['post'][$key]['type']) {
                case 'numberfield':
                    $value = preg_match("/^[0-9]+\.[0-9]+$/", $value) ? floatval($value) : intval($value);
                    break;
                case 'datefield':
                    $value = preg_match("/^[0-9]+$/", $value) ? new \MongoDate(intval($value)) : new \MongoDate(strtotime($value));
                    break;
                case '2dfield':
                    $value = is_array($value) ? array(
                        floatval($value['lng']),
                        floatval($value['lat'])
                    ) : array(
                        0,
                        0
                    );
                    break;
                default:
                    $value = trim($value);
                    break;
            }
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
            
            if (! empty($detail['rshCollection']))
                $exact = true;
            
            if (isset($_REQUEST[$field])) {
                if (is_array($_REQUEST[$field]) && trim(join('', $_REQUEST[$field])) == '')
                    continue;
                
                if (! is_array($_REQUEST[$field]) && trim($_REQUEST[$field]) == '')
                    continue;
                
                switch ($detail['type']) {
                    case 'numberfiled':
                        $min = trim($_REQUEST[$field]['min']);
                        $max = trim($_REQUEST[$field]['max']);
                        $min = preg_match("/^[0-9]+\.[0-9]+$/", $min) ? floatval($min) : intval($min);
                        $max = preg_match("/^[0-9]+\.[0-9]+$/", $max) ? floatval($max) : intval($max);
                        if ($not) {
                            if (! empty($min))
                                $subQuery['$or'][$field]['$lte'] = $min;
                            if (! empty($max))
                                $subQuery['$or'][$field]['$gte'] = $max;
                        } else {
                            if (! empty($min))
                                $subQuery[$field]['$gte'] = $min;
                            if (! empty($max))
                                $subQuery[$field]['$lte'] = $max;
                        }
                        break;
                    case 'datefield':
                        $start = trim($_REQUEST[$field]['start']);
                        $end = trim($_REQUEST[$field]['end']);
                        $start = preg_match("/^[0-9]+$/", $start) ? new \MongoDate(intval($start)) : new \MongoDate(strtotime($start));
                        $end = preg_match("/^[0-9]+$/", $end) ? new \MongoDate(intval($end)) : new \MongoDate(strtotime($end));
                        if ($not) {
                            if (! empty($start))
                                $subQuery['$or'][$field]['$lte'] = $start;
                            if (! empty($end))
                                $subQuery['$or'][$field]['$gte'] = $end;
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
     * @param string $name            
     * @throws \Exception or string
     */
    private function getCollectionIdByName($name)
    {
        try {
            new \MongoId($name);
            return $name;
        } catch (\MongoException $ex) {}
        
        $collectionInfo = $this->_collection->findOne(array(
            'project_id' => $this->_project_id,
            'name' => $name
        ));
        
        if ($collectionInfo == null) {
            throw new \Exception('集合名称不存在于指定项目');
        }
        
        return $collectionInfo['_id']->__toString();
    }
}
