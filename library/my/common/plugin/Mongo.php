<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use My\Common\Mongo;

class M extends AbstractPlugin
{

    public function __invoke($collection = null, $database = 'ICCv1', $cluster = 'default')
    {
        if ($message === null)
            return $this;
        return $this->collection($collection, $database, $cluster);
    }

    public function collection($collection, $database = 'ICCv1', $cluster = 'default')
    {
        $mongoConfig = $this->getController()
            ->getServiceLocator()
            ->get('mongos');
        
        return new Mongo($mongoConfig, $collection, $database, $cluster);
    }
}