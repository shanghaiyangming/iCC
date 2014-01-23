<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Key extends Mongo
{
     
    protected $collection = IDATABASE_KEYS;

}