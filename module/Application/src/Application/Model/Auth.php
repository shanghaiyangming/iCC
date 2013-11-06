<?php
namespace Application\Model;

use My\Common\MongoAbstract;
use Zend\Config\Config;

class Auth extends MongoAbstract
{

    public function __construct(Config $config)
    {
        if (empty($this->_collection))
            $this->_collection = strtolower(str_replace(array(
                __NAMESPACE__,
                '\\'
            ), '', __CLASS__));
        
        parent::__construct($config);
    }
}