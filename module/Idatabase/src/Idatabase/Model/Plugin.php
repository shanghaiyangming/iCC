<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;
use My\Common\MongoCollection;

class Plugin extends Mongo
{

    private $_structure;
    
    protected $collection = IDATABASE_PLUGINS;
    
    public function init() {
        $this->_structure = new Structure($this->config);
    }

    /**
     * 同步全部plugin_id的数据结构
     *
     * @param string $plugin_id            
     */
    public function sync($plugin_id)
    {
        
    }
}