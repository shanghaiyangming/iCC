<?php
namespace Logs\Service;


use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MonologServiceInitializer implements InitializerInterface
{

    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof MonologServiceAwareInterface) {
            $instance->setMonologService($serviceLocator->get('LogMongodbService'));
        }
    }
}