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
     * 初始化函数
     *
     * @see \My\Common\ActionController::init()
     */
    public function init()
    {
        resetTimeMemLimit();
        $this->_project_id = isset($_REQUEST['project_id']) ? trim($_REQUEST['project_id']) : '';
        
        if (empty($this->_project_id))
            throw new \Exception('$this->_project_id值未设定');
        
        $this->_collection = $this->model(IDATABASE_COLLECTIONS);
        $this->_collection_id = isset($_REQUEST['collection_id']) ? trim($_REQUEST['collection_id']) : '';
        if (empty($this->_collection_id))
            throw new \Exception('$this->_collection_id值未设定');
        
        $this->_collection_id = $this->getCollectionIdByName($this->_collection_id);
        $this->_collection_name = 'idatabase_collection_' . $this->_collection_id;
        
        $this->_data = $this->model($this->_collection_name);
        $this->_structure = $this->model(IDATABASE_STRUCTURES);
        
        $this->_schema = $this->getSchema();
        $this->_order = $this->model(IDATABASE_COLLECTION_ORDERBY);
    }

    /**
     * 导入数据到集合内
     * 
     */
    public function importAction() {
        try {
            $cursor = $this->_structure->find(array('collection_id'=>$this->_collection_id));
            if($cursor->count()==0) {
                return $this->msg(false, '集合数据结构未定义');
                return false;
            }
             
            $formStructure = array();
            while($cursor->hasNext()) {
                $row = $cursor->getNext();
                $formStructure[$row['alias']] = $row;
                $formStructure[$row['name']] = $row;
            }
             
            if($_FILES['import']['error']==UPLOAD_ERR_OK) {
                $fileName = $_FILES['import']['name'];
                $filePath = $_FILES['import']['tmp_name'];
                $importSheetName = trim($_POST['sheetName']);
                if($importSheetName=='') {
                    return $this->msg(false, '请设定需要导入的sheet');
                }
        
                switch (strtolower(pathinfo($fileName,PATHINFO_EXTENSION))) {
                    case 'xls':
                        $inputFileType = 'Excel5';
                        break;
                    case 'xlsx':
                        $inputFileType = 'Excel2007';
                        break;
                    case 'xml':
                        $inputFileType = 'Excel2003XML';
                        break;
                    default:
                        return $this->msg(false, '很抱歉，您上传的文件格式无法识别');
                }
        
                include_once 'MyReadFilter.php';
                include_once 'PHPExcel/PHPExcel.php';
                include_once 'PHPExcel/PHPExcel/IOFactory.php';
        
        
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objReader->setLoadSheetsOnly($importSheetName);
        
                $objPHPExcel = $objReader->load($filePath);
                if(!in_array($importSheetName,array_values($objPHPExcel->getSheetNames()))) {
                    return $this->msg(false, 'Sheet:"'.$importSheetName.'",不存在，请检查您导入的Excel表格');
                }
                $objPHPExcel->setActiveSheetIndexByName($importSheetName);
                $objActiveSheet = $objPHPExcel->getActiveSheet();
                $sheetData = $objActiveSheet->toArray(null,true,true,true);
                $objPHPExcel->disconnectWorksheets();
        
                unset($objReader,$objPHPExcel,$objActiveSheet);
                gc_collect_cycles();//回收内存
        
                if(empty($sheetData)) {
                    return $this->msg(false, '请确认表格中包含数据');
                }
        
                $getFieldError = 0 ;
                $title = array_shift($sheetData);
        
        
                $fieldInfo = array();
                foreach($title as $key=>$cell) {
                    $cell = trim($cell);
                    if(isset($formStructure[$cell])) {
                        $fieldInfo[$key] = array('type'=>$formStructure[$cell]['type'],'field'=>$formStructure[$cell]['alias']);
                    }
                    else {
                        $getFieldError = 1;
                        break;
                    }
                }
        
                if($getFieldError==1)
                    return $this->msg(false, '检测到未知数据列：“'.$cell.'”,如果未知列为空，请在excel表格中使用ctrl+end，检查是否存在空数据列。');
        
        
                array_walk($sheetData,function($row,$rowNumber) use($fieldInfo) {
                    $tmp = array('createTime'=>new MongoDate());
                    foreach($fieldInfo as $k=>$field) {
                        $tmp[$field['field']] = $this->convertDatas($row[$k],$field['type']);
                    }
                    $this->_iDatabaseRecord->insert($tmp);
                    unset($tmp);
                });
        
                unset($sheetData);
                echo json_encode(array('success'=>true,'msg'=>'导入数据成功'));
            }
            else {
                $this->msg(false, '上传文件失败');
            }
        }
        catch (Exception $e) {
            fb(exceptionMsg($e),\FirePHP::LOG);
            $this->msg(false, '导入失败，发生异常');
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
            
            if ($row['rshKey'])
                $this->_schema['combobox']['rshCollectionKeyField'] = $row['field'];
            
            if ($row['rshValue'])
                $this->_schema['combobox']['rshCollectionValueField'] = $row['field'];
            
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
                                $subQuery['$or'][][$field]['$lte'] = $min;
                            if (! empty($max))
                                $subQuery['$or'][][$field]['$gte'] = $max;
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
}
