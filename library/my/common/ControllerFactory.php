<?php
/**
 * 自动加载Controller
 * 
 * @author Young
 * https://samsonasik.wordpress.com/tag/automatic-controller-invokables/
 *
 */
namespace My\Common;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerFactory implements AbstractFactoryInterface
{

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param
     *            $name
     * @param
     *            $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (class_exists($requestedName . 'Controller'))
            return true;
        return false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator            
     * @param
     *            $name
     * @param
     *            $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $class = $requestedName . 'Controller';
        return new $class();
    }
}