<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use My\Common\MongoCollection;

class ModelMongoCollection extends AbstractPlugin
{

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
        return $this->collection($collection, $database, $cluster);
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
        $mongoConfig = $this->getController()
            ->getServiceLocator()
            ->get('mongos');
        
        return new MongoCollection($mongoConfig, $collection, $database, $cluster);
    }
}