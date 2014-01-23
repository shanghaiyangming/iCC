<?php
/**
 * 
 * @author Young
 *
 */
namespace My\Common\Model;

use Zend\Config\Config;
use My\Common\MongoCollection;

class Mongo
{
    /**
     * 自动化初始MongoCollection实例，用于外部调用
     * @var object
     */
    public $model;
    
    /**
     * 集群环境配置信息
     * @var Config
     */
    protected $config;

    /**
     * 需要调用的集合名称
     * @var string
     */
    protected $collection = null;

    /**
     * 数据库名称
     * @var string
     */
    protected $database = DEFAULT_DATABASE;

    /**
     * 集群名称
     * @var string
     */
    protected $cluster = DEFAULT_CLUSTER;

    /**
     * 初始化相关配置
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
}