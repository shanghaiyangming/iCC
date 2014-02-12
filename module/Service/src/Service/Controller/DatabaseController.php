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
        $className = 'My\Common\Service\Database';
        echo $this->soap($uri, $className, $params);
        return $this->response;
    }
}

