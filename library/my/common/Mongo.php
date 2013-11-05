<?php
namespace My\Common;

use Zend\Config\Config;

abstract class Mongo extends \MongoCollection
{

    protected $_collection = '';

    protected $_database = 'ICCv1';

    protected $_cluster = 'default';

    private $_db;

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
        $this->_db = $this->_config[$this->_cluster]['dbs'][$this->_database];
        parent::__construct($this->_db, $this->_collection);
    }

    /**
     *
     * @param array $object            
     * @param array $options            
     */
    public function insert(array $object = NULL, Array $options = NULL)
    {
        if ($object === NULL)
            throw new \Exception('$object is NULL');
        
        $default = array(
            'fsync' => self::fsync,
            'timeout' => self::timeout
        );
        if ($options == NULL)
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
        if ($options == NULL)
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
        if ($options == NULL)
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
    public function findAndModifyByCommand($option, $collection = null)
    {
        $cmd = array();
        $targetCollection = $collection === null ? $this->_collection : $collection;
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
     *
     * @param array $rst            
     * @return 处理包含mongo数据为完全的数组，去掉里面的对象类型的数据转化为相应的字符串，如mongoid进行toString() mongodate进行date处理等
     */
    public function convertToPureArray($rst)
    {
        return convertToPureArray($rst);
    }

    /**
     * 打印最后一个错误信息
     */
    public function debug()
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
     * 直接禁止drop操作
     *
     * @see MongoCollection::drop()
     */
    public function drop()
    {
        throw new \Exception('ICC deny execute "drop()" collection operation');
    }

    /**
     * 在析构函数中调用debug方法
     */
    public function __destruct()
    {
        $this->debug();
    }
}