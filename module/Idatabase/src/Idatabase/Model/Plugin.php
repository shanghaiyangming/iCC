<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;
use My\Common\MongoCollection;

class Plugin extends Mongo
{

    private $_structure;

    private $_collection;

    private $_project;

    private $_mapping;

    protected $collection = IDATABASE_PLUGINS;

    public function init()
    {
        $this->_structure = new Structure($this->config);
        $this->_collection = new Collection($this->config);
        $this->_project = new Project($this->config);
        $this->_mapping = new Mapping($this->config);
    }

    public function getOne($plugin_id)
    {
        if (! ($plugin_id instanceof \MongoId)) {
            $plugin_id = myMongoId($plugin_id);
        }
        
        return $this->model->findOne(array(
            '_id' => myMongoId($plugin_id)
        ));
    }

    /**
     * 同步全部plugin_id的文档自定义结构
     *
     * @param string $plugin_id            
     */
    public function sync($plugin_id)
    {}

    /**
     * 同步指定项目的指定插件
     *
     * @param string $project_id            
     * @param string $plugin_id            
     * @return true false
     */
    public function syncProjectPlugin($project_id, $plugin_id)
    {}
}