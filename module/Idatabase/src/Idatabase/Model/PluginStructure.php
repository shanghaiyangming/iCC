<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class PluginStructure extends Mongo
{
     
    protected $collection = IDATABASE_PLUGINS_STRUCTURES;

}