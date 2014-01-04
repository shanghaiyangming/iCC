<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Plugin extends Mongo
{

    protected $collection = IDATABASE_PLUGINS;

    /**
     * 同步全部plugin_id的数据结构
     * 
     * @param string $plugin_id            
     */
    public function sync($plugin_id)
    {}

    public function syncStructure()
    {}

    public function syncKey()
    {}
    
    public function syncStatistic()
    {}
    
}