<?php
namespace Idatabase\Model;

use My\Common\Model\Mongo;

class Plugin extends Mongo
{

    private $_project_plugin;

    private $_plugin_collection;
    
    private $_plugin_structure;

    private $_structure;

    private $_collection;

    private $_project;

    private $_mapping;

    protected $collection = IDATABASE_PLUGINS;

    public function init()
    {
        $this->_project_plugin = new ProjectPlugin($this->config);
        $this->_plugin_collection = new PluginCollection($this->config);
        $this->_plugin_structure = new PluginStructure($this->config);
        $this->_structure = new Structure($this->config);
        $this->_collection = new Collection($this->config);
        $this->_project = new Project($this->config);
        $this->_mapping = new Mapping($this->config);
    }

    /**
     * 同步全部plugin_id的文档自定义结构
     *
     * @param string $plugin_id            
     */
    public function syncAll($plugin_id)
    {}

}