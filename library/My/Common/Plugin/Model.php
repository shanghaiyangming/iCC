<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use My\Common\MongoCollection;
use My\Common\Model\Mongo;

class Model extends AbstractPlugin
{
    private $_mongoConfig;
    
    private $_collection = null;
    
    private $_database = 'ICCv1';
    
    private $_cluster = 'default';

    /**
     * 初始化插件并执行初始化集合调用
     * @param string $collection
     * @param string $database
     * @param string $cluster
     * @return \My\Common\Plugin\ModelMongoCollection|\My\Common\MongoCollection
     */
    public function __invoke($collection = null, $database = 'ICCv1', $cluster = 'default')
    {
        if ($collection === null)
            return $this;
        
        $this->_collection = $collection;
        $this->_database = $database;
        $this->_cluster = $cluster;
        return $this->collection($this->_collection, $this->_database, $this->_cluster);
    }

    /**
     * 初始化集合调用
     * @param string $collection
     * @param string $database
     * @param string $cluster
     * @return \My\Common\MongoCollection
     */
    public function collection($collection, $database = 'ICCv1', $cluster = 'default')
    {
        $this->_mongoConfig = $this->getController()
            ->getServiceLocator()
            ->get('mongos');
        
        $this->_collection = $collection;
        $this->_database = $database;
        $this->_cluster = $cluster;
        
        return new MongoCollection($this->_mongoConfig, $this->_collection, $this->_database, $this->_cluster);
    }
    
    /**
     *
     */
    public function instance($modelName) {
        if(class_exists($modelName)) {
            return new $modelName($this->_mongoConfig,$this->_collection, $this->_database, $this->_cluster);
        }
        else {
            return new MongoCollection($this->_mongoConfig, $this->_collection, $this->_database, $this->_cluster);
        }
    }
}