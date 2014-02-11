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

    private $_verify;

    private $_project_id;

    private $_collection_name;

    private $_rand;

    public function __construct(Config $config)
    {
        $this->_config = $config;
        $this->_key = new MongoCollection($config, IDATABASE_KEYS);
        $this->_mapping = new MongoCollection($config, IDATABASE_MAPPING);
    }

    /**
     * 身份认证，请在SOAP HEADER部分请求该函数进行身份校验
     *
     * @param string $project_id
     * @param string $rand
     *            随机数,建议保持一定的长度（10位以上，可以考虑Unix时间戳）
     * @param string $sign
     *            签名算法:md5($project_id.$rand.$sign) 请转化为长度为32位的16进制字符串
     * @return boolean
     */
    public function authenticate($project_id, $rand, $sign, $key_id = 'default')
    {
        $this->_project_id = $project_id;
        $info = $this->getKeysInfo($project_id);

        if (md5($project_id . $rand . $info['password']) == strtolower($sign)) {
            $this->_verify = true;
            $this->model = new MongoCollection($config, $collection = null, $database = DEFAULT_DATABASE, $cluster = DEFAULT_CLUSTER, $collectionOptions = null);
            return true;
        }
        return false;
    }

    private function getKeysInfo($key_id='default') {

    }

    public function count()
    {}

    public function find()
    {}

    public function findOne()
    {}

    public function findAll()
    {}

    public function distinct()
    {}

    public function save()
    {}

    public function insert()
    {}

    public function batchInsert()
    {}

    public function update()
    {}

    public function remove()
    {}

    public function drop()
    {}

    public function ensureIndex()
    {}

    public function deleteIndex()
    {}

    public function deleteIndexes()
    {}

    public function findAndModify()
    {}

    public function group()
    {}

    public function aggregate()
    {}

    public function __destruct()
    {}
}