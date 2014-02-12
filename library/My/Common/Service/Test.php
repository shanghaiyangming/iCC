<?php
/**
 * iCC新版idatabase服务
 * @author yangming
 *
 */
namespace My\Common\Service;

use My\Common\MongoCollection;
use Zend\Config\Config;

class Test
{

    private $_c = 0;

    public function __construct($c)
    {
        $this->_c = $c;
    }

    /**
     * 加法运算
     * 
     * @param int $a            
     * @param int $b            
     * @return int
     */
    public function sum($a, $b)
    {
        return intval($a + $b + $this->_c);
    }
}