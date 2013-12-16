<?php
namespace Idatabase\Model;

use Zend\Config\Config;

class Plugin extends ModelMongo
{
    protected $collection = IDATABASE_PLUGINS;

    /**
     * 同步全部plugin_id的数据结构
     * @param string $plugin_id
     */
    public function sync($plugin_id) {
        
    }
}