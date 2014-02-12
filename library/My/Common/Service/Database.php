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

    private $_project_id;

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
     * @param string $rand 随机数,建议保持一定的长度（10位以上，可以考虑Unix时间戳）
     * @param string $sign 签名算法:md5($project_id.$rand.$sign) 请转化为长度为32位的16进制字符串
     * @param string $sign
     * @return boolean
     */
    public function authenticate($project_id, $rand, $sign, $key_id = null)
    {
        $this->_project_id = $project_id;
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

    public function count($name)
    {}

    public function find($form,$query,$sort,$skip,$limit,$fields)
    {}

    public function findOne($form,$query,$fields)
    {}

    public function findAll($form,$query,$sort,$fields)
    {}

    public function distinct($form,$key,$query)
    {}

    public function save()
    {}

    public function insert($form,$datas)
    {}

    public function batchInsert()
    {}

    public function update($form,$criteria,$object)
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

    public function findAndModify($form,$options)
    {}

    public function group()
    {}

    public function aggregate($form,$ops)
    {}

    public function __destruct()
    {}
}