<?php
namespace My\Common\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Log extends AbstractPlugin
{

    public function __invoke($message = null, $level = 100, $context = array())
    {
        if ($message === null)
            return $this;
        return $this->logger($message, $level, $context);
    }

    public function logger($message, $level = 100, $context = array())
    {
        return $this->getController()
            ->getServiceLocator()
            ->get('LogMongodbService')
            ->addRecord($level, $message, $context);
    }
}