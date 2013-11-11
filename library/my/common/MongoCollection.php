<?php
/**
 * 用于扩展基础类库
 * @author yangming
 *
 */
namespace My\Common;

use Zend\Config\Config;
use Doctrine\Tests\Common\Annotations\True;

class MongoCollection extends \MongoCollection
{

    private $_collection = '';

    private $_database = 'ICCv1';

    private $_cluster = 'default';

    private $_collectionOptions = NULL;

    private $_db;

    private $_admin;

    private $_config;

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

    public function __construct(Config $config, $collection = null, $database = 'ICCv1', $cluster = 'default', $collectionOptions = null)
    {
        if ($collection === null) {
            throw new \Exception('$collection is null');
        }
        
        $this->_collection = $collection;
        $this->_database = $database;
        $this->_cluster = $cluster;
        $this->_collectionOptions = $collectionOptions;
        $this->_config = $config->toArray();
        
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
            
            // 默认执行几个操作
            // 第一个操作，判断集合是否创建，如果没有创建，则进行分片处理（目前采用_ID作为片键）
            // $this->shardingCollection();
        parent::__construct($this->_db, $this->_collection);
    }

    /**
     * 对于新建集合进行自动分片
     *
     * @return boolean
     */
    private function shardingCollection()
    {
        $defaultCollectionOptions = array(
            'capped' => false, // 是否开启固定集合
            'size' => pow(1024, 3), // 如果简单开启capped=>true,单个集合的最大尺寸为1G
            'max' => pow(10, 7), // 如果简单开启capped=>true,单个集合的最大条数为1千万条数据
            'autoIndexId' => true
        );
        
        if ($this->_collectionOptions !== NULL) {
            $this->_collectionOptions = array_merge($defaultCollectionOptions, $this->_collectionOptions);
        }
        
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

    public function search($text)
    {
        $search = new MongoRegex('/' . preg_replace("/\s/", '.*', $text) . '/i');
    }

    /**
     * 获取符合条件的全部数据
     *
     * @param array $query            
     * @param int $skip            
     * @param int $limit            
     * @param array $sort            
     * @return array
     */
    public function findAll($query, $skip = 0, $limit = 20, $sort = array('_id'=>-1))
    {
        $cursor = $this->find($query);
        if (! $cursor instanceof \MongoCursor)
            throw new \Exception('$query error:' . json_encode($query));
        
        $cursor->sort($sort)
            ->skip($skip)
            ->limit($limit);
        return convertToPureArray(iterator_to_array($cursor));
    }

    /**
     * 插入特定的数据
     *
     * @param array $object            
     * @param array $options            
     */
    public function insert($a, array $options = NULL)
    {
        if (empty($a))
            throw new \Exception('$object is NULL');
        
        $default = array(
            'fsync' => self::fsync,
            'timeout' => self::timeout
        );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        
        return parent::insert($a, $options);
    }

    /**
     * 更新指定范围的数据
     *
     * @param array $criteria            
     * @param array $object            
     * @param array $options            
     */
    public function update($criteria, $object, array $options = NULL)
    {
        if (empty($criteria))
            throw new \Exception('$criteria is empty');
        
        if (empty($object))
            throw new \Exception('$object is empty');
        
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
     * 删除指定范围的数据
     *
     * @param array $criteria            
     * @param array $options            
     */
    public function remove($criteria = NULL, array $options = NULL)
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
     * @param array $option            
     * @param string $collection            
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
        // 做法1：抛出异常禁止Drop操作
        // throw new \Exception('ICC deny execute "drop()" collection operation');
        // 做法2：复制整个集合的数据到新的集合中，用于备份，备份数据不做片键，不做索引以便节约空间，仅出于安全考虑，原有_id使用保留字__OLD_ID__进行保留
        $targetCollection = 'bak_' . date('YmdHis') . '_' . $this->_collection;
        $target = new \MongoCollection($this->db, $targetCollection);
        // 变更为重命名某个集合或者复制某个集合的操作作为替代。
        $cursor = $this->find(array());
        while ($cursor->hasNext()) {
            $row = $cursor->getNext();
            $row['__OLD_ID__'] = $row['_id'];
            unset($row['_id']);
            $target->insert($row);
        }
        return parent::drop();
    }

    /**
     * ICC系统默认采用后台创建的方式，建立索引
     *
     * @see MongoCollection::ensureIndex()
     */
    public function ensureIndex($key_keys, array $options = NULL)
    {
        $default = array(
            'background' => True
        // 'expireAfterSeconds'=>3600, //请充分了解后开启此参数，慎用
                );
        if ($options === NULL)
            $options = $default;
        else
            $options = array_merge($default, $options);
        
        return parent::ensureIndex($key_keys, $options);
    }

    /**
     * 打印最后一个错误信息
     */
    private function debug()
    {
        $err = $this->_db->lastError();
        if (self::debug)
            var_dump($err);
            // 记录下每一条产生的mongodb错误日志
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