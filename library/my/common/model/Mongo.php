<?php
namespace My\Common\Model;

use Zend\Config\Config;

class Mongo
{
    protected $model;
    
    protected $collection = null;
    
    protected $database = 'ICCv1';
    
    protected $cluster = 'default';
    
    public function __construct(Config $config)
    {
        if($collection==null) {
            throw new \Exception('请设定你要操作的集合');
        }
        
        $this->model = new MongoCollection($config,$this->collection,$this->database,$this->cluster);
    }
}