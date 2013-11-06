<?php
/**
 * 用于扩展基础类库
 * @author yangming
 *
 */
namespace My\Common;

use Zend\Config\Config;
use Doctrine\Tests\Common\Annotations\True;

abstract class Mongo extends \MongoCollection
{

    protected $_collection = '';

    protected $_database = 'ICCv1';

    protected $_cluster = 'default';

    protected $_collectionOptions = NULL;

    private $_db;

    private $_admin;

    private $_updateHaystack = array(
        '$set',
        '$inc',
        '$unset',
        '$rename',
        '$setOnInsert',
        '$addToSet',
        '$pop',
        '$pullAll',
        '$pull',
        '$pushAll',
        '$push',
        '$each',
        '$slice',
        '$sort',
        '$bit',
        '$isolated'
    );

    const timeout = 6000000;

    const fsync = false;

    const upsert = false;

    const multiple = true;

    const justOne = false;

    const debug = false;

    public function __construct(Config $config)
    {
        $config = $config->toArray();
        if (! isset($this->_config[$this->_cluster]))
            throw new \Exception('Config error:no cluster key');
        
        if (! isset($this->_config[$this->_cluster]['dbs'][$this->_database]))
            throw new \Exception('Config error:no database init');
        
        $this->_db = $this->_config[$this->_cluster]['dbs'][$this->_database];
        if (! $this->_db instanceof \MongoDB)
            throw new \Exception('$this->_db is not instanceof \MongoDB');
        
        if (! isset($this->_config[$this->_cluster]['dbs']['admin']))
            throw new \Exception('Config error:admin database init');
        
        $this->_admin = $this->_config[$this->_cluster]['dbs']['admin'];
        if (! $this->_admin instanceof \MongoDB)
            throw new \Exception('$this->_admin is not instanceof \MongoDB');
        
        parent::__construct($this->_db, $this->_collection);
        
        if ($this->_collectionOptions === NULL) {
            $this->_collectionOptions = array(
                'capped' => false,
                'size' => pow(1024, 3),
                'max' => pow(10, 7),
                'autoIndexId' => true
            );
        }
        
        // 默认执行几个操作
        // 第一个操作，判断集合是否创建，如果没有创建，则进行分片处理（目前采用_ID作为片键）
        $this->shardingCollection();
    }

    private function shardingCollection()
    {
        $this->_db->createCollection($this->_collection, $this->_collectionOptions);
        $rst = $this->_admin->command(array(
            'shardCollection' => $this->_database . '.' . $this->_collection,
            'key' => array(
                '_id' => 1
            )
        ));
        if ($rst['ok'] == 1) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param array $object            
     * @param array $options            
     */
    public function insert(Array $object = NULL, Array $options = NULL)
    {
        if ($object === NULL)
            throw new \Exception('$object is NULL');
        
        $default = array(
            'fsync' => self::fsync,
            'timeout' => self::timeout
        );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        
        return parent::insert($object, $options);
    }

    /**
     *
     * @param array $criteria            
     * @param array $object            
     * @param array $options            
     */
    public function update(Array $criteria = NULL, Array $object = NULL, Array $options = NULL)
    {
        if ($criteria === NULL)
            throw new \Exception('$criteria is NULL');
        
        if ($object === NULL)
            throw new \Exception('$object is NULL');
        
        $keys = array_keys($object);
        foreach ($keys as $key) {
            $key = strtolower($key);
            if (! in_array($key, $this->_updateHaystack)) {
                throw new \Exception('$key must contain ' . join(',', $this->_updateHaystack));
            }
        }
        $default = array(
            'upsert' => self::upsert,
            'multiple' => self::multiple,
            'fsync' => self::fsync,
            'timeout' => self::timeout
        );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        
        return parent::update($criteria, $object, $options);
    }

    /**
     *
     * @param array $criteria            
     * @param array $options            
     */
    public function remove(Array $criteria = NULL, Array $options = NULL)
    {
        if ($criteria === NULL)
            throw new \Exception('$criteria is NULL');
        
        $default = array(
            'justOne' => self::justOne,
            'fsync' => self::fsync,
            'timeout' => self::timeout
        );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        return parent::remove($criteria, $options);
    }

    /**
     * 增加findAndModify方法
     *
     * @param
     *            $option
     * @param
     *            $collection
     * @return mixed array|null
     */
    public function findAndModifyByCommand($option, $collection = NULL)
    {
        $cmd = array();
        $targetCollection = $collection === NULL ? $this->_collection : $collection;
        $cmd['findandmodify'] = $targetCollection;
        if (isset($option['query']))
            $cmd['query'] = $option['query'];
        if (isset($option['sort']))
            $cmd['sort'] = $option['sort'];
        if (isset($option['remove']))
            $cmd['remove'] = is_bool($option['remove']) ? $option['remove'] : false;
        if (isset($option['update']))
            $cmd['update'] = $option['update'];
        if (isset($option['new']))
            $cmd['new'] = is_bool($option['new']) ? $option['new'] : false;
        if (isset($option['fields']))
            $cmd['fields'] = $option['fields'];
        if (isset($option['upsert']))
            $cmd['upsert'] = is_bool($option['upsert']) ? $option['upsert'] : false;
        return $this->_db->command($cmd);
    }

    /**
     * 直接禁止drop操作
     *
     * @see MongoCollection::drop()
     */
    public function drop()
    {
        throw new \Exception('ICC deny execute "drop()" collection operation');
        // 变更为重命名某个集合或者复制某个集合的操作作为替代。
        $this->_admin->command();
    }
    
    /**
     * ICC系统默认采用后台创建的方式，建立索引
     * @see MongoCollection::ensureIndex()
     */
    public function ensureIndex($key_keys,$options = NULL) {
        $default = array(
            'background' => True,
            //'expireAfterSeconds'=>3600, //请充分了解后开启此参数，慎用
        );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        
        return parent::ensureIndex($key_keys,$options);
    }
    

    /**
     *
     * @param array $rst
     * @return 处理包含mongo数据为完全的数组，去掉里面的对象类型的数据转化为相应的字符串，如mongoid进行toString() mongodate进行date处理等
     */
    private function convertToPureArray($rst)
    {
        return convertToPureArray($rst);
    }
    
    /**
     * 打印最后一个错误信息
     */
    private function debug()
    {
        $err = $this->_db->lastError();
        if (self::debug)
            var_dump($err);
        //记录下每一条产生的mongodb错误日志
        if ($err['err'] != null) {
            logError(json_encode($err));
        }
    }

    /**
     * 在析构函数中调用debug方法
     */
    public function __destruct()
    {
        $this->debug();
    }
}