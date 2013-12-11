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
namespace My\Common;

use Zend\Config\Config;
use Zend\EventManager\GlobalEventManager;

class MongoCollection extends \MongoCollection
{

    private $_collection = '';

    private $_database = 'ICCv1';

    private $_cluster = 'default';

    private $_collectionOptions = NULL;

    private $_db;

    private $_admin;

    private $_config;

    /**
     * GridFS
     * @var MongoGridFS
     */
    private $_fs;

    private $_queryHaystack = array(
        '$and',
        '$or',
        '$nor',
        '$not',
        '$where'
    );

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
        if (! $this->_admin instanceof \MongoDB) {
            throw new \Exception('$this->_admin is not instanceof \MongoDB');
        }
        
        $this->_fs = new \MongoGridFS($this->_db, "icc");
        
        // 默认执行几个操作
        // 第一个操作，判断集合是否创建，如果没有创建，则进行分片处理（目前采用_ID作为片键）
        if (APPLICATION_ENV === 'production') {
            $this->shardingCollection();
        }
        parent::__construct($this->_db, $this->_collection);
    }

    /**
     * 检测是简单查询还是复杂查询，涉及复杂查询
     *
     * @param array $query            
     * @throws \Exception
     */
    private function appendQuery(array $query = null)
    {
        if (! is_array($query)) {
            $query = array();
        }
        $keys = array_keys($query);
        $intersect = array_intersect($keys, $this->_queryHaystack);
        if (! empty($intersect)) {
            $query = array(
                '$and' => array(
                    array(
                        '__REMOVED__' => false
                    ),
                    $query
                )
            );
        } else {
            $query['__REMOVED__'] = false;
        }
        return $query;
    }

    /**
     * 打印最后一个错误信息
     */
    private function debug()
    {
        $err = $this->_db->lastError();
        if (self::debug) {
            var_dump($err);
        }
        // 记录下每一条产生的mongodb错误日志
        if ($err['err'] != null) {
            GlobalEventManager::trigger('logError', null, array(
                json_encode($err)
            ));
        }
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

    /**
     * 处理检索条件
     *
     * @param string $text            
     */
    private function search($text)
    {
        return new MongoRegex('/' . preg_replace("/[\s\r\t\n]/", '.*', $text) . '/i');
    }

    /**
     * 批量插入数据
     *
     * @see MongoCollection::batchInsert()
     */
    public function batchInsert(array $documents, array $options = NULL)
    {
        array_walk($documents, function (&$row, $key)
        {
            $row['__CREATE_TIME__'] = $row['__MODIFY_TIME__'] = new \MongoDate();
            $row['__REMOVED__'] = false;
        });
        return parent::batchInsert($documents, $options);
    }

    /**
     * 统计符合条件的数量
     *
     * @see MongoCollection::count()
     */
    public function count($query = NULL, $limit = NULL, $skip = NULL)
    {
        $query = $this->appendQuery($query);
        return parent::count($query, $limit, $skip);
    }

    /**
     * 根据指定字段
     *
     * @param string $key            
     * @param array $query            
     */
    public function distinct($key, $query = null)
    {
        $query = $this->appendQuery($query);
        return parent::distinct($key, $query);
    }

    /**
     * 直接禁止drop操作
     *
     * @see MongoCollection::drop()
     */
    function drop()
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
        $default = array();
        $default['background'] = true;
        // $default['expireAfterSeconds'] = 3600; // 请充分了解后开启此参数，慎用
        $options = ($options === NULL) ? $default : array_merge($default, $options);
        return parent::ensureIndex($key_keys, $options);
    }

    /**
     * 查询符合条件的项目，自动排除__REMOVED__:true的结果集
     *
     * @see MongoCollection::find()
     */
    public function find($query = NULL, $fields = NULL)
    {
        $fields = $fields == null ? array() : $fields;
        return parent::find($this->appendQuery($query), $fields);
    }

    /**
     * 查询符合条件的一条数据
     *
     * @see MongoCollection::findOne()
     */
    public function findOne($query = NULL, $fields = NULL)
    {
        $fields = $fields == null ? array() : $fields;
        return parent::findOne($this->appendQuery($query), $fields);
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
    public function findAll($query, $skip = 0, $limit = 20, $sort = array('_id'=>-1), $fields = array())
    {
        $cursor = $this->find($this->appendQuery($query), $fields);
        if (! $cursor instanceof \MongoCursor)
            throw new \Exception('$query error:' . json_encode($query));
        
        $cursor->sort($sort)
            ->skip($skip)
            ->limit($limit);
        return iterator_to_array($cursor);
    }

    /**
     * findAndModify操作
     * 特别注意：__REMOVED__ __MODIFY_TIME__ __CREATE_TIME__ 3个系统保留变量在update参数中的使用
     *
     * @param array $query            
     * @param array $update            
     * @param array $fields            
     * @param array $options            
     * @return array
     */
    public function findAndModify(array $query, array $update = NULL, array $fields = NULL, array $options = NULL)
    {
        $query = $this->appendQuery($query);
        return parent::findAndModify($query, $update, $fields, $options);
    }

    /**
     * 增加findAndModify方法
     * 特别注意：__REMOVED__ __MODIFY_TIME__ __CREATE_TIME__ 3个系统保留变量在update参数中的使用
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
            $cmd['query'] = $this->appendQuery($option['query']);
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
        $options = ($options === NULL) ? $default : array_merge($default, $options);
        
        array_unset_recursive($a, array(
            '__CREATE_TIME__',
            '__MODIFY_TIME__',
            '__REMOVED__'
        ));
        
        if (! isset($a['__CREATE_TIME__'])) {
            $a['__CREATE_TIME__'] = new \MongoDate();
        }
        
        if (! isset($a['__MODIFY_TIME__'])) {
            $a['__MODIFY_TIME__'] = new \MongoDate();
        }
        
        if (! isset($a['__REMOVED__'])) {
            $a['__REMOVED__'] = false;
        }
        
        return parent::insert($a, $options);
    }

    /**
     * 通过findAndModify的方式，插入数据。
     * 这样可以使用$a['a.b']的方式插入结构为{a:{b:xxx}}的数据,这是insert所不能办到的
     * 采用update也可以实现类似的效果，区别在于findAndModify可以返回插入之后的新数据，更接近insert的原始行为
     *
     * @param array $a            
     * @return array
     */
    public function insertByFindAndModify($a)
    {
        if (empty($a))
            throw new \Exception('$a is NULL');
        
        array_unset_recursive($a, array(
            '__CREATE_TIME__',
            '__MODIFY_TIME__',
            '__REMOVED__'
        ));
        
        if (! isset($a['__CREATE_TIME__'])) {
            $a['__CREATE_TIME__'] = new \MongoDate();
        }
        
        if (! isset($a['__MODIFY_TIME__'])) {
            $a['__MODIFY_TIME__'] = new \MongoDate();
        }
        
        if (! isset($a['__REMOVED__'])) {
            $a['__REMOVED__'] = false;
        }
        
        $query = array(
            '_id' => new \MongoId()
        );
        $a = array(
            '$set' => $a
        );
        $fields = null;
        $options = array(
            'new' => true,
            'upsert' => true
        );
        
        return parent::findAndModify($query, $a, $fields, $options);
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
        
        $options = ($options === NULL) ? $default : array_merge($default, $options);
        
        // 方案一 真实删除
        // return parent::remove($criteria, $options);
        // 方案二 伪删除
        $criteria = $this->appendQuery($criteria);
        return parent::update($criteria, array(
            '$set' => array(
                '__REMOVED__' => true
            )
        ), $options);
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
        
        $options = ($options === NULL) ? $default : array_merge($default, $options);
        
        $criteria = $this->appendQuery($criteria);
        array_unset_recursive($object, array(
            '__CREATE_TIME__',
            '__MODIFY_TIME__',
            '__REMOVED__'
        ));
        
        if (parent::count($criteria) == 0) {
            parent::update($criteria, array(
                '$set' => array(
                    '__CREATE_TIME__' => new \MongoDate(),
                    '__MODIFY_TIME__' => new \MongoDate(),
                    '__REMOVED__' => false
                )
            ), $options);
        } else {
            parent::update($criteria, array(
                '$set' => array(
                    '__MODIFY_TIME__' => new \MongoDate(),
                    '__REMOVED__' => false
                )
            ), $options);
        }
        return parent::update($criteria, $object, $options);
    }

    /**
     * 云存储文件
     * @param array $file $_FILES['name']
     */
    public function storeToGridFS($fieldName, $metadata = array())
    {
        if (! isset($_FILES[$fieldName]))
            throw new \Exception('$_FILES[$fieldName]无效');
        
        $metadata = array_merge($metadata, $_FILES[$fieldName]);
        $id = $this->_fs->storeUpload($fieldName, $metadata);
        $gridfsFile = $this->_fs->get($id);
        return $gridfsFile->file;
    }
    
    /**
     * 存储二进制内容
     * @param bytes $bytes
     * @param array $metadata
     */
    public function storeBytesToGridFS($bytes,$metadata = array()) {
        $id = $this->_fs->storeBytes($bytes, $metadata);
        $gridfsFile = $this->_fs->get($id);
        return $gridfsFile->file;
    }

    /**
     * 根据ID获取文件的信息
     * @param string $id
     * @return array 文件信息数组
     */
    public function getInfoFromGridFS($id)
    {
        if (! $id instanceof \MongoId) {
            $id = new \MongoId($id);
        }
        $gridfsFile = $this->_fs->get($id);
        return $gridfsFile->file;
    }

    /**
     * 根据ID获取文件内容，二进制
     * @param string $id   
     * @return bytes         
     */
    public function getFileFromGridFS($id)
    {
        if (! $id instanceof \MongoId) {
            $id = new \MongoId($id);
        }
        $gridfsFile = $this->_fs->get($id);
        return $gridfsFile->getBytes();
    }
    
    /**
     * 删除陈旧的文件
     * @param mixed $id \MongoID or String
     * @return bool true or false
     */
    public function removeFileFromGridFS($id) {
        if (! $id instanceof \MongoId) {
            $id = new \MongoId($id);
        }
        return $this->_fs->remove($id);
    }

    /**
     * 在析构函数中调用debug方法
     */
    public function __destruct()
    {
        $this->debug();
    }
}