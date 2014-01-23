<?php
namespace Idatabase\Model;

use My\Common\Model\Mongo;

class PluginCollection extends Mongo
{
     
    protected $collection = IDATABASE_PLUGINS_COLLECTIONS;

}