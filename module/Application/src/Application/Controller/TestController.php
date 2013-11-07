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

class TestController extends AbstractActionController
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
        echo $this->params()->fromRoute('r');
        phpinfo();
        return $this->response;
    }
    
    public function cacheAction() {
        var_dump($this->getServiceLocator()->has('coreCache'));
        //$cache = $this->getServiceLocator()->get('Application\Cache');
        if(($data = $cache->load('key'))===false) {
            $data = array(time());
            $cache->save($data);
        }
        
        var_dump($data);
    }
    
    public function mongoAction()
    {
        $db = $this->getServiceLocator()->get('mongos');
        return $this->response;
    }
    
    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function insertMongoAction()
    {
        try {
            $this->_mongos = $this->getServiceLocator()->get('mongos');
            $this->_model = new Auth($this->_mongos);
            if ($this->_model instanceof \MongoCollection)
                echo '$this->_model instanceof \MongoCollection';
            else
                echo 'error';
            var_dump($this->_model->insert(array(
            'a' => time()
            )));
            var_dump($this->_model->findOne());
            echo 'OK';
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        return $this->response;
    }
    
    /**
     * 登录验证码生成
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function captchaAction()
    {
        $builder = new CaptchaBuilder();
        // $builder->setBackgroundColor($r, $g, $b);
        // $builder->build($width = 150, $height = 40);
        $builder->build(150, 40);
        $_SESSION['phrase'] = $builder->getPhrase();
        // $builder->output($quality = 80);
        header('Content-type: image/jpeg');
        $this->response->setContent($builder->output(80));
        return $this->response;
    }
    
    public function logAction() {
        //var_dump($this->getServiceLocator()->get('EnliteMonologService'));
        //var_dump($this->getServiceLocator()->get('LogMongodbService')->addDebug('hello world'));
        //var_dump($this->log()->logger('OK plugin'));
        var_dump($this->log('123'));
    
        return $this->response;
    }
}
