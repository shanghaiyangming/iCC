<?php
namespace Application\Model;

use My\Common\Mongo;

class Auth extends Mongo
{

    public function __construct()
    {
        if (empty($this->_collection))
            $this->_collection = strtolower(str_replace(array(
                __NAMESPACE__,
                '\\'
            ), '', __CLASS__));
        
        $config = array();
        parent::__construct($config);
    }
}