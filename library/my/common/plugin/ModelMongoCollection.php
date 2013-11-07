<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use My\Common\MongoCollection;

class ModelMongoCollection extends AbstractPlugin
{

    public function __invoke($collection = null, $database = 'ICCv1', $cluster = 'default')
    {
        if ($collection === null)
            return $this;
        return $this->collection($collection, $database, $cluster);
    }

    public function collection($collection, $database = 'ICCv1', $cluster = 'default')
    {
        $mongoConfig = $this->getController()
            ->getServiceLocator()
            ->get('mongos');
        
        return new MongoCollection($mongoConfig, $collection, $database, $cluster);
    }
}