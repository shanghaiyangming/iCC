<?php
/**
 * iDatabase服务
 *
 * @author young 
 * @version 2014.02.12
 * 
 */
namespace Service\Controller;

use My\Common\Controller\Action;

class DatabaseController extends Action
{

    public function indexAction()
    {
        $uri = 'http://localhost/service/database/index';
        $className = 'My\Common\Service\Test';
        $config = $this->getServiceLocator()->get('mongos');
        echo $this->soap($uri, $className, $config);
        return $this->response;
    }
}

