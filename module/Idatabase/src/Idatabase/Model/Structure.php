<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Structure extends Mongo
{
     
    protected $collection = IDATABASE_STRUCTURES;

}