<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Plugin extends Mongo
{

    protected $collection = IDATABASE_PLUGINS;
    
    /**
     * 初始化
     * @param Config $config
     */
    public function __construct(Config $config) {
        parent::__construct($config);
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