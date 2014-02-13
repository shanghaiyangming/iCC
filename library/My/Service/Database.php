<?php
/**
 * iDatabase服务
 * @author yangming
 * 
 */
namespace My\Service;

use My\Common\MongoCollection;
use Zend\Config\Config;
use Zend\Json\Json;

class Database
{

    private $_model = null;

    private $_config = null;

    private $_key = null;

    private $_mapping = null;

    private $_collection = null;

    private $_structure = null;

    private $_project_id = null;

    private $_collection_id = null;

    public function __construct(Config $config)
    {
        $this->_config = $config;
        $this->_key = new MongoCollection($config, IDATABASE_KEYS);
    }

    /**
     * 身份认证，请在SOAP HEADER部分请求该函数进行身份校验
     * 签名算法:md5($project_id.$rand.$sign) 请转化为长度为32位的16进制字符串
     *
     * @param string $project_id            
     * @param string $rand            
     * @param string $sign            
     * @param string $key_id            
     * @throws \SoapFault
     * @return boolean
     */
    public function authenticate($project_id, $rand, $sign, $key_id = null)
    {
        if (strlen($rand) < 8) {
            throw new \SoapFault(411, '随机字符串长度过短，为了安全起见至少8位');
        }
        $this->_project_id = $project_id;
        $key_id = ! empty($key_id) ? $key_id : null;
        $keyInfo = $this->getKeysInfo($project_id, $key_id);
        if (md5($project_id . $rand . $keyInfo['key']) !== strtolower($sign)) {
            throw new \SoapFault(401, '身份认证校验失败');
        }
        return true;
    }

    /**
     * 获取密钥信息
     *
     * @param string $project_id            
     * @param string $key_id            
     * @throws \SoapFault
     * @return multitype:
     */
    private function getKeysInfo($project_id, $key_id)
    {
        $query = array();
        $query['project_id'] = $project_id;
        if ($key_id !== null) {
            $query['_id'] = myMongoId($key_id);
        } else {
            $query['default'] = true;
        }
        $query['expire'] = array(
            '$gte' => new \MongoDate()
        );
        $query['active'] = true;
        $rst = $this->_key->findOne($query, array(
            'key' => true
        ));
        if ($rst === null)
            throw new \SoapFault(404, '授权密钥无效');
        return $rst;
    }

    /**
     * 设定集合名称，请在SOAP HEADER部分进行设定
     *
     * @param string $collectionAlias            
     * @throws \SoapFault
     * @return boolean
     */
    public function setCollection($collectionAlias)
    {
        $this->_collection = new MongoCollection($this->_config, IDATABASE_COLLECTIONS);
        $this->_mapping = new MongoCollection($this->_config, IDATABASE_MAPPING);
        
        $collectionInfo = $this->_collection->findOne(array(
            'project_id' => $this->_project_id,
            'alias' => $collectionAlias
        ));
        if ($collectionInfo === null) {
            throw new \SoapFault(404, '访问集合不存在');
        }
        
        $this->_collection_id = myMongoId($collectionInfo['_id']);
        $mapping = $this->_mapping->findOne(array(
            'project_id' => $this->_project_id,
            'collection_id' => $this->_collection_id,
            'active' => true
        ));
        if ($mapping === null) {
            $this->_model = new MongoCollection($this->_config, iCollectionName($this->_collection_id));
        } else {
            $this->_model = new MongoCollection($this->_config, $mapping['collection'], $mapping['database'], $mapping['cluster']);
        }
        return true;
    }

    /**
     * 获取当前集合的文档结构
     *
     * @throws \SoapFault
     */
    public function getSchema()
    {
        if ($this->_collection_id == null)
            throw new \SoapFault(500, '$_collection_id不存在');
        
        if ($this->_project_id == null)
            throw new \SoapFault(500, '$_project_id不存在');
        
        $this->_structure = new MongoCollection($this->_config, IDATABASE_STRUCTURES);
        $cursor = $this->_structure->find(array(
            'collection_id' => $this->_collection_id
        ));
        
        if ($cursor->count() == 0)
            throw new \SoapFault(500, '集合未定义文档结构');
        
        return convertToPureArray(iterator_to_array($cursor, false));
    }

    /**
     * 统计数量
     *
     * @param array $query            
     * @return int
     */
    public function count($query)
    {
        $query = $this->jsonToArray($query);
        return intval($this->_model->count($query));
    }

    /**
     * 查询特定范围
     *
     * @param string $query            
     * @param string $sort            
     * @param int $skip            
     * @param int $limit            
     * @param string $fields            
     * @return array
     */
    public function find($query, $sort, $skip, $limit, $fields)
    {
        $query = $this->jsonToArray($query);
        $sort = $this->jsonToArray($sort);
        $skip = intval($skip) > 0 ? intval($skip) : 0;
        $limit = intval($limit) < 0 ? 10 : intval($limit);
        $limit = intval($limit) > 1000 ? 1000 : intval($limit);
        if (isJson($fields)) {
            $fields = $this->jsonToArray($fields);
        } else {
            $fields = array();
        }
        
        $rst = $this->_model->findAll($query, $sort, $skip, $limit, $fields);
        return convertToPureArray($rst);
    }

    /**
     * 查询某一个项
     *
     * @param string $query            
     * @param string $fields            
     * @return array
     */
    public function findOne($query, $fields)
    {
        $query = $this->jsonToArray($query);
        if (isJson($fields)) {
            $fields = $this->jsonToArray($fields);
        } else {
            $fields = array();
        }
        $rst = $this->_model->findOne($query, $fields);
        return convertToPureArray($rst);
    }

    /**
     * 查询全部数据
     *
     * @param string $query            
     * @param string $sort            
     * @param string $fields            
     */
    public function findAll($query, $sort, $fields)
    {
        $query = $this->jsonToArray($query);
        $sort = $this->jsonToArray($sort);
        if (isJson($fields)) {
            $fields = $this->jsonToArray($fields);
        } else {
            $fields = array();
        }
        $rst = $this->_model->findAll($query, $sort, 0, 0, $fields);
        return convertToPureArray($rst);
    }

    /**
     * 某一列唯一的数据
     *
     * @param string $key            
     * @param string $query            
     */
    public function distinct($key, $query)
    {
        $key = is_string($key) ? trim($key) : '';
        $query = $this->jsonToArray($query);
        $rst = $this->_model->distinct($key, $query);
        return convertToPureArray($rst);
    }

    /**
     * 保存数据，$datas中如果包含_id属性，那么将更新_id的数据，否则创建新的数据
     *
     * @param string $datas            
     * @throws \SoapFault
     * @return array
     */
    public function save($datas)
    {
        $datas = $this->jsonToArray($datas);
        $rst = $this->_model->save($datas);
        return convertToPureArray($rst);
    }

    /**
     * 插入数据
     *
     * @param string $datas            
     * @throws \SoapFault
     * @return array
     */
    public function insert($datas)
    {
        $datas = $this->jsonToArray($datas);
        $rst = $this->_model->insertByFindAndModify($datas);
        return convertToPureArray($rst);
    }

    /**
     * 批量插入
     * @param string $a            
     * @throws \SoapFault
     * @return array
     */
    public function batchInsert($a)
    {
        $a = $this->jsonToArray($a);
        $rst = $this->_model->batchInsert($datas);
        return convertToPureArray($rst);
    }

    /**
     * 更新操作
     * @param string $criteria            
     * @param string $object            
     * @throws \SoapFault
     * @return array
     */
    public function update($criteria, $object)
    {
        $criteria = $this->jsonToArray($criteria);
        $object = $this->jsonToArray($object);
        $rst = $this->_model->update($criteria, $object);
        return convertToPureArray($rst);
    }

    /**
     * 删除操作
     * @param string $criteria
     * @throws \SoapFault
     * @return array
     */
    public function remove($criteria)
    {
        $criteria = $this->jsonToArray($criteria);
        return $this->_model->remove($criteria);
    }

    /**
     * 清空整个集合
     */
    public function drop()
    {
        $this->_model->drop();
    }

    /**
     * 创建索引
     * @param string $keys
     * @param string $options
     * @return boolean
     */
    public function ensureIndex($keys, $options)
    {
        if (isJson($keys)) {
            $keys = $this->jsonToArray($keys);
        } else {
            $keys = trim($keys);
        }
        
        if (isJson($options)) {
            $options = $this->jsonToArray($options);
        } else {
            $options = array(
                'background' => true
            );
        }
        return $this->_model->ensureIndex($keys, $options);
    }

    /**
     * 删除特定索引
     *
     * @return array
     */
    public function deleteIndex($keys)
    {
        $keys = $this->jsonToArray($keys);
        return $this->_model->deleteIndex($keys);
    }

    /**
     * 删除全部索引
     *
     * @return array
     */
    public function deleteIndexes()
    {
        return $this->_model->deleteIndexes();
    }

    public function findAndModify($options)
    {
        $options = $this->jsonToArray($options);
        $rst = $this->_model->findAndModifyByCommand($options);
        return convertToPureArray($rst);
    }
    
    /**
     * aggregate框架支持
     * 
     * @param string $ops1
     * @param string $ops2
     * @param string $ops3
     * @return array
     */
    public function aggregate($ops1,$ops2,$ops3)
    {
        $param_arr = array();
        $param_arr[] = array();
        
        $ops1 = $this->jsonToArray($ops1);
        $param_arr[] = $ops1;
        if(!empty($ops2)) {
            $ops2 = $this->jsonToArray($ops2);
            $param_arr[] = $ops2;
        }
        if(!empty($ops3)) {
            $ops3 = $this->jsonToArray($ops3);
            $param_arr[] = $ops3;
        }

        $rst = call_user_func_array(array($this->_model,'aggregate'), $param_arr);
        return convertToPureArray($rst);
    }

    /**
     * 将json字符串转化为数组
     * @param string $json
     * @throws \SoapFault
     * @return Ambigous <\Zend\Json\mixed, mixed, NULL, \Zend\Json\$_tokenValue, multitype:, stdClass, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL, multitype:, stdClass> , multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, multitype:, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL, multitype:, stdClass> , NULL, stdClass> >
     */
    private function jsonToArray($json)
    {
        if (! isJson($json))
            throw new \SoapFault(500, 'json格式不正确，无法转化为PHP数组，请检查json格式');
        return Json::decode($json, Json::TYPE_ARRAY);
    }

    public function __destruct()
    {}
}