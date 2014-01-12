<?php
/**
 * 扩展和限定基础类库操作
 * @author yangming
 *
 * 使用说明：
 *
 * 对于mongocollection的操作进行了规范，危险方法的drop remove均采用了伪删除的实现。
 * 删除操作时，remove实际上是添加了保留属性__REMOVED__设置为true
 * 添加操作时，额外添加了保留属性__CREATE_TIME__(创建时间) 和 __MODIFY_TIME__(修改时间) __REMOVED__：false
 * 更新操作时，将自动更新__MODIFY_TIME__
 * 查询操作时,count/find/findOne/findAndModify操作 ，查询条件将自动添加__REMOVED__:false参数，编码时，无需手动添加
 *
 * 注意事项：
 *
 * 1. findAndModify内部的操作update时，请手动添加__MODIFY_TIME__ __CREATE_TIME__ 原因详见mongodb的upsert操作说明，我想看完你就理解了
 * 2. group、aggregate操作因为涉及到里面诸多pipe细节，考虑到代码的可读性、简洁以及易用性，所以请手动处理__MODIFY_TIME__ __CREATE_TIME__ __REMOVED__ 三个保留参数
 * 3. 同理，对于db->command操作内部，诸如mapreduce等操作时，如涉及到数据修改，请注意以上三个参数的变更与保留，以免引起不必要的问题。
 *
 */
namespace My\Common\Soa;

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
     * @return bool
     */
    public function authenticate($project_id, $rand, $sign)
    {
        $this->_project_id = $project_id;
        $info = $this->getProjectInfo($project_id);
        
        if (md5($project_id . $rand . $info['password']) == strtolower($sign)) {
            $this->_verify = true;
            $this->model = new MongoCollection($config, $collection = null, $database = DEFAULT_DATABASE, $cluster = DEFAULT_CLUSTER, $collectionOptions = null);
            return true;
        }
        return false;
    }

    public function __destruct()
    {}
}