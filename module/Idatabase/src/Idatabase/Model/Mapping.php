<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Mapping extends Mongo
{
     
    protected $collection = IDATABASE_MAPPING;

}