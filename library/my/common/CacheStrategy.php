<?php
namespace My\Common;

use Zend\Cache\StorageFactory;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class CacheListener implements ListenerAggregateInterface
{

    protected $cache;

    protected $listeners = array();

    public function __construct(StorageFactory $cache)
    {
        $this->cache = $cache;
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('get.pre', array(
            $this,
            'load'
        ), 100);
        $this->listeners[] = $events->attach('get.post', array(
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

    public function load(EventInterface $e)
    {
        $id = get_class($e->getTarget()) . '-' . json_encode($e->getParams());
        if (null !== ($content = $this->cache->getItem($id))) {
            $e->stopPropagation(true);
            return $content;
        }
    }

    public function save(EventInterface $e)
    {
        $params = $e->getParams();
        $content = $params['__RESULT__'];
        unset($params['__RESULT__']);
        
        $id = get_class($e->getTarget()) . '-' . json_encode($params);
        $this->cache->setItem($id, $content);
    }
}