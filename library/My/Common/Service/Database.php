<?php
/**
 * iCC新版idatabase服务
 * @author yangming
 *
 */
namespace My\Common\Service;

use My\Common\MongoCollection;
use Zend\Config\Config;

class Database
{

    private $_model;

    private $_config;

    private $_key;

    private $_mapping;

    private $_collection;

    private $_project_id;

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
        
        $mapping = $this->_mapping->findOne(array(
            'project_id' => $this->_project_id,
            'collection_id' => myMongoId($collectionInfo['_id']),
            'active' => true
        ));
        if ($mapping === null) {
            $this->_model = new MongoCollection($this->_config, iCollectionName($collectionInfo['_id']));
        } else {
            $this->_model = new MongoCollection($this->_config, $mapping['collection'], $mapping['database'], $mapping['cluster']);
        }
        return true;
    }

    /**
     * 统计数量
     * 
     * @param array $query            
     * @return int
     */
    public function count($query)
    {
        return intval($this->_model->count($query));
    }
    
    // public function find($form, $query, $sort, $skip, $limit, $fields)
    // {}
    
    // public function findOne($form, $query, $fields)
    // {}
    
    // public function findAll($form, $query, $sort, $fields)
    // {}
    
    // public function distinct($form, $key, $query)
    // {}
    
    // public function save()
    // {}
    
    // public function insert($form, $datas)
    // {}
    
    // public function batchInsert($form)
    // {}
    
    // public function update($form, $criteria, $object)
    // {}
    
    // public function remove()
    // {}
    
    // public function drop()
    // {}
    
    // public function ensureIndex()
    // {}
    
    // public function deleteIndex()
    // {}
    
    // public function deleteIndexes()
    // {}
    
    // public function findAndModify($form, $options)
    // {}
    
    // public function group()
    // {}
    
    // public function aggregate($form, $ops)
    // {}
    public function __destruct()
    {}
}