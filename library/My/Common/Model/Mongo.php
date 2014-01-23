<?php
/**
 * Model基类
 * @author Young
 * 说明:
 * 1. 自动初始化链接默认是集群
 * 2. 初始化后，如果init方法存在，自动调用init方法
 * 3. 采用过载的方式调用公开变量$model中的方法,$model为MongoCollection示例
 * 4. 因为3中采用的是过载方式调用MongoCollection的方法，所以如果Model中定义了MongoCollection同名方法时,将执行基类中方法，而不是$model中方法
 */
namespace My\Common\Model;

use Zend\Config\Config;
use My\Common\MongoCollection;

class Mongo
{

    /**
     * 自动化初始MongoCollection实例，用于外部调用
     *
     * @var object
     */
    public $model;

    /**
     * 集群环境配置信息
     *
     * @var Config
     */
    protected $config;

    /**
     * 需要调用的集合名称
     *
     * @var string
     */
    protected $collection = null;

    /**
     * 数据库名称
     *
     * @var string
     */
    protected $database = DEFAULT_DATABASE;

    /**
     * 集群名称
     *
     * @var string
     */
    protected $cluster = DEFAULT_CLUSTER;

    /**
     * 初始化相关配置
     *
     * @param Config $config            
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        if ($this->collection == null) {
            throw new \Exception('请设定你要操作的集合');
        }
        
        $this->config = $config;
        $this->model = new MongoCollection($config, $this->collection, $this->database, $this->cluster);
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * 过载处理
     * 
     * @param string $name            
     * @param mixed $arguments            
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->model, $name)) {
            return call_user_func_array(array(
                $this->model,
                $name
            ), $arguments);
        }
    }
}