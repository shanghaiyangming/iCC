<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Cache extends AbstractPlugin
{

    private $_cache;

    public function __invoke($key = null)
    {
        $this->_cache = $this->getController()
            ->getServiceLocator()
            ->get(CACHE_ADAPTER);
        
        if ($key === null) {
            return $this->_cache;
        }
        return $this->load($key);
    }

    public function load($key)
    {
        return $this->_cache->getItem($key);
    }

    public function save($datas, $key)
    {
        return $this->_cache->setItem($key, $data);
    }

    public function remove()
    {
        return $this->_cache->removeItem($key);
    }
}