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

    private $_collection;

    private $_fieldRgex = '/^[a-z]{1}[a-z0-9_\.]*$/i';

    public function init()
    {
        if ($this->action != 'filter') {
            $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
            
            if (empty($this->_project_id)) {
                throw new \Exception('$this->_project_id值未设定');
            }
            
            $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
            if (empty($this->_collection_id)) {
                throw new \Exception('$this->_collection_id值未设定');
            }
        }
        
        $this->_structure = $this->model(IDATABASE_STRUCTURES);
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
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
        
        $sort = array(
            'orderBy' => 1,
            '_id' => 1
        );
        
        $rst = array();
        $cursor = $this->_structure->find($query);
        $cursor->sort($sort);
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            if (isset($row['rshCollection']) && $row['rshCollection'] != '') {
                $row = array_merge($row, $this->getRshCollectionInfo($row['rshCollection']));
            }
            
            // 追加读取quick字段
            if (!empty($row['isQuick']) && !empty($row['quickSourceCollection']) && !empty($row['quickTargetCollection'])) {
                $row['__QUICK__'] = array(
                    'drag' => array(
                        'collectionName' => $row['quickSourceCollection'],
                        'structure' => $this->getRshCollectionInfo($row['quickSourceCollection']),
                        'query' => $row['quickSearchCondition']
                    ),
                    'drop' => array(
                        'collectionName' => $row['quickTargetCollection'],
                        'structure' => $this->getRshCollectionInfo($row['quickTargetCollection']),
                        'query' => $row['quickSearchCondition']
                    )
                );
            }
            
            $rst[] = $row;
        }
        
        return $this->rst($rst, $cursor->count(), true);
    }

    /**
     * 获取指定集合的集合结构
     *
     * @param string $collectionName            
     * @return array
     */
//     private function getStructure($collectionName)
//     {
//         $cursor = $this->_structure->find(array(
//             'collection_id' => $this->getCollectionIdByName($collectionName)
//         ));
//         return iterator_to_array($cursor, false);
//     }

    /**
     * 获取关联集合的信息
     *
     * @param string $collectionName            
     * @return array
     */
    private function getRshCollectionInfo($collectionName)
    {
        $rst = array();
        $cursor = $this->_structure->find(array(
            'collection_id' => $this->getCollectionIdByName($collectionName)
        ));
        
        $rst = array(
            'rshCollectionValueField' => '_id'
        );
        
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            if ($row['rshKey'])
                $rst['rshCollectionDisplayField'] = $row['field'];
            if ($row['rshValue'])
                $rst['rshCollectionValueField'] = $row['field'];
        }
        return $rst;
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
        $datas['plugin_collection_id'] = $this->params()->fromPost('plugin_collection_id', '');
        $datas['plugin_id'] = $this->params()->fromPost('plugin_id', '');
        $datas['field'] = $this->params()->fromPost('field', null);
        $datas['label'] = $this->params()->fromPost('label', null);
        $datas['type'] = $this->params()->fromPost('type', null);
        $datas['filter'] = (int) filter_var($this->params()->fromPost('filter', 0), FILTER_SANITIZE_NUMBER_INT);
        $datas['searchable'] = filter_var($this->params()->fromPost('searchable', false), FILTER_VALIDATE_BOOLEAN);
        $datas['main'] = filter_var($this->params()->fromPost('main', false), FILTER_VALIDATE_BOOLEAN);
        $datas['required'] = filter_var($this->params()->fromPost('required', false), FILTER_VALIDATE_BOOLEAN);
        $datas['isFatherField'] = filter_var($this->params()->fromPost('isFatherField', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshCollection'] = $this->params()->fromPost('rshCollection', '');
        $datas['rshType'] = 'combobox';
        $datas['rshKey'] = filter_var($this->params()->fromPost('rshKey', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshValue'] = filter_var($this->params()->fromPost('rshValue', false), FILTER_VALIDATE_BOOLEAN);
        $datas['showImage'] = filter_var($this->params()->fromPost('showImage', false), FILTER_VALIDATE_BOOLEAN);
        $datas['orderBy'] = (int) filter_var($this->params()->fromPost('orderBy', 0), FILTER_VALIDATE_INT);
        $datas['isQuick'] = filter_var($this->params()->fromPost('isQuick', false), FILTER_VALIDATE_BOOLEAN);
        $datas['quickSourceCollection'] = trim($this->params()->fromPost('quickSourceCollection', ''));
        $datas['quickTargetCollection'] = trim($this->params()->fromPost('quickTargetCollection', ''));
        $datas['quickSearchCondition'] = trim($this->params()->fromPost('quickSearchCondition', ''));
        
        if ($datas['field'] == null) {
            return $this->msg(false, '请填写字段名称');
        }
        
        if (! $this->checkFieldName($datas['field'])) {
            return $this->msg(false, '字段名必须为以英文字母开始的“字母、数字、下划线”的组合,“点”标注子属性时，子属性必须以字母开始');
        }
        
        if ($datas['label'] == null) {
            return $this->msg(false, '请填写字段描述');
        }
        
        if ($datas['type'] == null) {
            return $this->msg(false, '请选择字段类型');
        }
        
        if ($datas['isQuick'] === true) {
            if ($datas['type'] !== 'documentfield') {
                return $this->msg(false, '快速录入字段，输入类型必须是“子文档结构”');
            }
            
            if ($datas['quickSourceCollection'] === '') {
                return $this->msg(false, '请选快速录入的数据来源集合');
            }
            
            if ($datas['quickTargetCollection'] === '') {
                return $this->msg(false, '请选快速录入的目标集合');
            }
            
            if ($datas['quickSearchCondition'] !== '') {
                if (isJson($datas['quickSearchCondition'])) {
                    try {
                        $datas['quickSearchCondition'] = Json::decode($datas['quickSearchCondition'], Json::TYPE_ARRAY);
                    } catch (\Exception $e) {
                        $this->msg(false, '快速录入查询条件的json格式错误');
                    }
                } else {
                    return $this->msg(false, '快速录入查询条件的json格式错误');
                }
            }
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
        $datas['plugin_collection_id'] = $this->params()->fromPost('plugin_collection_id', '');
        $datas['plugin_id'] = $this->params()->fromPost('plugin_id', '');
        $datas['field'] = $this->params()->fromPost('field', null);
        $datas['label'] = $this->params()->fromPost('label', null);
        $datas['type'] = $this->params()->fromPost('type', null);
        $datas['filter'] = (int) filter_var($this->params()->fromPost('filter', 0), FILTER_SANITIZE_NUMBER_INT);
        $datas['searchable'] = filter_var($this->params()->fromPost('searchable', false), FILTER_VALIDATE_BOOLEAN);
        $datas['main'] = filter_var($this->params()->fromPost('main', false), FILTER_VALIDATE_BOOLEAN);
        $datas['required'] = filter_var($this->params()->fromPost('required', false), FILTER_VALIDATE_BOOLEAN);
        $datas['isFatherField'] = filter_var($this->params()->fromPost('isFatherField', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshCollection'] = $this->params()->fromPost('rshCollection', '');
        $datas['rshType'] = 'combobox';
        $datas['rshKey'] = filter_var($this->params()->fromPost('rshKey', false), FILTER_VALIDATE_BOOLEAN);
        $datas['rshValue'] = filter_var($this->params()->fromPost('rshValue', false), FILTER_VALIDATE_BOOLEAN);
        $datas['showImage'] = filter_var($this->params()->fromPost('showImage', false), FILTER_VALIDATE_BOOLEAN);
        $datas['orderBy'] = (int) filter_var($this->params()->fromPost('orderBy', 0), FILTER_VALIDATE_INT);
        $datas['isQuick'] = filter_var($this->params()->fromPost('isQuick', false), FILTER_VALIDATE_BOOLEAN);
        $datas['quickSourceCollection'] = trim($this->params()->fromPost('quickSourceCollection', ''));
        $datas['quickTargetCollection'] = trim($this->params()->fromPost('quickTargetCollection', ''));
        $datas['quickSearchCondition'] = trim($this->params()->fromPost('quickSearchCondition', ''));
        
        if ($datas['field'] == null) {
            return $this->msg(false, '请填写字段名称');
        }
        
        if (! $this->checkFieldName($datas['field'])) {
            return $this->msg(false, '字段名必须为以英文字母开始的“字母、数字、下划线”的组合,“点”标注子属性时，子属性必须以字母开始');
        }
        
        if ($datas['label'] == null) {
            return $this->msg(false, '请填写字段描述');
        }
        
        if ($datas['type'] == null) {
            return $this->msg(false, '请选择字段类型');
        }
        
        if ($datas['isQuick'] === true) {
            if ($datas['type'] !== 'documentfield') {
                return $this->msg(false, '快速录入字段，输入类型必须是“子文档结构”');
            }
            
            if ($datas['quickSourceCollection'] === '') {
                return $this->msg(false, '请选快速录入的数据来源集合');
            }
            
            if ($datas['quickTargetCollection'] === '') {
                return $this->msg(false, '请选快速录入的目标集合');
            }
            
            if ($datas['quickSearchCondition'] !== '') {
                if (isJson($datas['quickSearchCondition'])) {
                    try {
                        $datas['quickSearchCondition'] = Json::decode($datas['quickSearchCondition'], Json::TYPE_ARRAY);
                    } catch (\Exception $e) {
                        $this->msg(false, '快速录入查询条件的json格式错误');
                    }
                } else {
                    return $this->msg(false, '快速录入查询条件的json格式错误');
                }
            }
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
     * 批量保存字段修改
     *
     * @author young
     * @name 批量保存字段修改
     * @version 2013.12.02 young
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
            
            if ($row['field'] == null) {
                return $this->msg(false, '请填写字段名称');
            }
            
            if (! $this->checkFieldName($row['field'])) {
                return $this->msg(false, '字段名必须为以英文字母开始的“字母、数字、下划线”的组合,“点”标注子属性时，子属性必须以字母开始');
            }
            
            if ($row['label'] == null) {
                return $this->msg(false, '请填写字段描述');
            }
            
            if ($row['type'] == null) {
                return $this->msg(false, '请选择字段类型');
            }
            
            if ($row['isQuick'] === true) {
                if ($row['type'] !== 'documentfield') {
                    return $this->msg(false, '快速录入字段，输入类型必须是“子文档结构”');
                }
                
                if ($row['quickSourceCollection'] === '') {
                    return $this->msg(false, '请选快速录入的数据来源集合');
                }
                
                if ($row['quickTargetCollection'] === '') {
                    return $this->msg(false, '请选快速录入的目标集合');
                }
                
                if ($row['quickSearchCondition'] !== '') {
                    if (isJson($row['quickSearchCondition'])) {
                        try {
                            $row['quickSearchCondition'] = Json::decode($row['quickSearchCondition'], Json::TYPE_ARRAY);
                        } catch (\Exception $e) {
                            $this->msg(false, '快速录入查询条件的json格式错误');
                        }
                    } else {
                        return $this->msg(false, '快速录入查询条件的json格式错误');
                    }
                }
            }
            
            $row['filter'] = (int) $row['filter'];
            
            $oldStructureInfo = $this->_structure->findOne(array(
                '_id' => myMongoId($_id)
            ));
            
            if ($this->checkExist('field', $row['field'], array(
                'collection_id' => $this->_collection_id
            )) && $oldStructureInfo['field'] != $row['field']) {
                return $this->msg(false, '字段名称已经存在');
            }
            
            if ($this->checkExist('label', $row['label'], array(
                'collection_id' => $this->_collection_id
            )) && $oldStructureInfo['label'] != $row['label']) {
                return $this->msg(false, '字段描述已经存在');
            }
            
            $this->_structure->update(array(
                '_id' => myMongoId($_id),
                'collection_id' => $this->_collection_id
            ), array(
                '$set' => $row
            ));
        }
        
        return $this->msg(true, '更新字段属性成功');
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
     * 获取全部过滤器方法
     *
     * @return multitype:number
     */
    public function filterAction()
    {
        $map = array();
        $map['int'] = '整数验证';
        $map['boolean'] = '是非验证';
        $map['float'] = '浮点验证';
        $map['validate_url'] = '是否URL';
        $map['validate_email'] = '是否Email';
        $map['validate_ip'] = '是否IP地址';
        $map['string'] = '过滤字符串';
        $map['encoded'] = '去除或编码特殊字符';
        $map['special_chars'] = 'HTML转义';
        $map['unsafe_raw'] = '无过滤字符串';
        $map['email'] = '过滤非Email字符';
        $map['url'] = '过滤非URL字符';
        $map['number_int'] = '数字过滤非整型';
        $map['number_float'] = '数字过滤非浮点';
        $map['magic_quotes'] = '转义字符';
        
        $filters = array();
        foreach (filter_list() as $key => $value) {
            if (isset($map[$value])) {
                $filters[] = array(
                    'name' => $map[$value],
                    'val' => filter_id($value)
                );
            }
        }
        
        $filters[] = array(
            'name' => '关闭过滤器',
            'val' => 0
        );
        return $this->rst($filters, null, true);
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

    /**
     * 检查mongodb的属性名称命名空间
     *
     * @param string $name            
     * @return boolean
     */
    private function checkFieldName($name)
    {
        if (! preg_match($this->_fieldRgex, $name)) {
            return false;
        }
        
        if (strpos($name, '.') !== false) {
            if (! preg_match("/\.[a-z]{1}/i", $name)) {
                return false;
            }
        }
        
        return true;
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
        } catch (\MongoException $ex) {
            // fb(exceptionMsg($ex),'LOG');
        }
        
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
