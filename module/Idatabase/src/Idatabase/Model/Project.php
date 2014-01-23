<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Project extends Mongo
{
     
    protected $collection = IDATABASE_PROJECTS;

}