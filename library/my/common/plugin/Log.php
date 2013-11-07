<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Log extends AbstractPlugin
{

    public function __invoke()
    {}

    public function logger($message, $level = 100, $context = array())
    {
        return $this->getController()
            ->getServiceLocator()
            ->get('LogMongodbService')
            ->addRecord($level, $message, $context);
    }
}