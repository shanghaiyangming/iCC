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

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $events = $this->getEventManager();
        echo $this->params()->fromRoute('index');
        echo $this->params()->fromQuery('get');
        echo $this->params()->fromFiles('file');
        echo $this->params()->fromRoute('r');
        $events->attach('do', function ($e)
        {
            $event = $e->getName();
            $params = $e->getParams();
            printf('Handled event "%s", with parameters %s', $event, json_encode($params));
        });
        $params = array(
            'foo' => 'bar',
            'baz' => 'bat'
        );
        $events->trigger('do', null, $params);
        return new ViewModel();
    }

    public function noViewAction()
    {
        phpinfo();
        return $this->response;
    }

    public function cacheAction()
    {
        $cache = $this->getServiceLocator()->get('coreCache');
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
}
