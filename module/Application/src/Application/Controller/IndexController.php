<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\GlobalEventManager;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $events = $this->getEventManager();
        echo $this->params()->fromRoute('index');
        echo $this->params()->fromQuery('get');
        echo $this->params()->fromFiles('file');
        echo $this->params()->fromRoute('r');
        $events->attach('do', function (EventInterface $e)
        {
            var_dump($e->getTarget());
            $event = $e->getName();
            $params = $e->getParams();
            printf('Handled event "%s", with parameters %s', $event, json_encode($params));
        });
        $params = array(
            'foo' => 'bar',
            'baz' => 'bat'
        );
        $events->trigger('do', array(), $params);
        return new ViewModel();
    }

    public function noViewAction()
    {
        phpinfo();
        return $this->response;
    }

    public function cacheAction()
    {
        $cache = $this->getServiceLocator()->get(CACHE_ADAPTER);
        if (($data = $cache->getItem('key')) === NULL) {
            $data = time();
            $cache->setItem('key',$data);
            echo 'no cache'.$data;
        }
        else {
            echo 'cache'.$data;
            $cache->removeItem('key');
        }
        return $this->response;
    }
    
    public function mongoAction() {
        $db = $this->getServiceLocator()->get('mongos');
        return $this->response;
    }
    
    public function triggerAction() {
<<<<<<< HEAD
        $events = $this->getEventManager();
        var_dump($events->getEvents());
        $params = array();
        $params = array_merge($params,$this->params()->fromQuery());
        $events->trigger('get.pre',$this,$params);
        $params['__RESULT__'] = 123;
        $events->trigger('get.post',$this,$this->params()->fromQuery());
        $this->response->setContent('<br />finished');
=======
        //$view = new ViewModel();
        //$view->setTerminal(true);
        
        $eventManager = GlobalEventManager::getEventCollection();
        $params = $this->params()->fromQuery();
        $result = $eventManager->trigger('cache.pre',null,$params);
        if($result->stopped()) {
            $content = 'cache'.$result->last();
            $this->response->setContent($content);
        }
        else {
            $content = 123;
            $params['__RESULT__'] = $content;
            $this->response->setContent($content);
            $eventManager->trigger('cache.post',null,$params);
        }
        
>>>>>>> b757ae26a44150bf52f75d7ae2c9fab6488cd0c3
        return $this->response;
    }
    
    public function staticEventAction() {
        $eventManager = new \Zend\EventManager\StaticEventManager();
        $eventManager::getInstance();
    }
    
}
