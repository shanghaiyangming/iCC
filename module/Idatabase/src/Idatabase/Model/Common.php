<?php
namespace Idatabase\Model;

use Zend\Config\Config;
use My\Common\Model\Mongo;

class Common extends Mongo
{

    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

}