<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Index extends Mongo
{
     
    protected $collection = IDATABASE_INDEXES;

}