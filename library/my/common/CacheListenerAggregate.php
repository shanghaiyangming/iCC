<?php
namespace My\Common;

use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class CacheListenerAggregate implements ListenerAggregateInterface
{

    protected $cache;

    private $_key = null;

    protected $listeners = array();
    

    public function __construct(AbstractAdapter $cache)
    {
        $this->cache = $cache;
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('cache.pre', array(
            $this,
            'load'
        ), 100);
        $this->listeners[] = $events->attach('cache.post', array(
            $this,
            'save'
        ), - 100);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function setKey(EventInterface $e)
    {
        $params = $e->getParams();
        if (isset($params) && array_key_exists('__RESULT__', $params)) {
            unset($params['__RESULT__']);
        }
        $this->_key = crc32(get_class($e->getTarget()) . '-' . json_encode($params));
    }

    public function getKey(EventInterface $e)
    {
        if ($this->_key == null) {
            $this->setKey($e);
        }
        return $this->_key;
    }

    public function load(EventInterface $e)
    {
        if (NULL !== ($content = $this->cache->getItem($this->getKey($e)))) {
            $e->stopPropagation(true);
            return $content;
        }
    }

    /**
     *
     * @method 保存缓存
     * @param EventInterface $e            
     * @param string $content            
     */
    public function save(EventInterface $e, $content = '')
    {
        $this->cache->setItem($this->getKey($e), $content);
    }
}