<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Collection extends Mongo
{
     
    protected $collection = IDATABASE_COLLECTIONS;

}