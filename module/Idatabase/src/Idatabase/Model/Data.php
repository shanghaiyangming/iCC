<?php
namespace Idatabase\Model;

use My\Common\Model\Mongo;

class Data extends Mongo
{
     
    protected $collection = '';
    
    public function __construct($config,$collection=null) {
        $this->config = $config;
        $this->collection = 'idatabase_collection_'.$this->collection;
        parent::__construct($config);
    }

}