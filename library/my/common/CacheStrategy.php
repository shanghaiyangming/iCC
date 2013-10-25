<?php 
use Zend\Cache\Cache;
use Zend\EventManager\EventCollection;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventInterface;

class CacheListener implements ListenerAggregateInterface
{
    protected $cache;

    protected $listeners = array();

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function attach(EventCollection $events)
    {
        $this->listeners[] = $events->attach('get.pre', array($this, 'load'), 100);
        $this->listeners[] = $events->attach('get.post', array($this, 'save'), -100);
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
        if (false !== ($content = $this->cache->load($id))) {
            $e->stopPropagation(true);
            return $content;
        }
    }

    public function save(EventInterface $e)
    {
        $params  = $e->getParams();
        $content = $params['__RESULT__'];
        unset($params['__RESULT__']);

        $id = get_class($e->getTarget()) . '-' . json_encode($params);
        $this->cache->save($content, $id);
    }
}