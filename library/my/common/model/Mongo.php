<?php
namespace My\Common\Model;

use Zend\Config\Config;
use My\Common\MongoCollection;

class Mongo
{
    protected $model;
    
    protected $collection = null;
    
    protected $database = 'ICCv1';
    
    protected $cluster = 'default';
    
    public function __construct(Config $config,$collection,$database='ICCv1',$cluster='default')
    {
        if($collection==null) {
            throw new \Exception('请设定你要操作的集合');
        }
        
        $this->collection = $collection;
        $this->database = $database;
        $this->cluster = $cluster;
        $this->model = new MongoCollection($config,$this->collection,$this->database,$this->cluster);
    }
}