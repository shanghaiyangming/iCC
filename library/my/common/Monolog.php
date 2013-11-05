<?php
namespace My\Common;

use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;

class Monolog
{
    private $_logger;
    
    /**
     * 
     * @param unknown $info
     */
    public function log($info) {
        $this->logger = new Logger();
    }
}