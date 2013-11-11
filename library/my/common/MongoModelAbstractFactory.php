<?php
/**
 * 自动加载Model
 * 
 * @author Young
 *
 */
namespace My\Common;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MongoModelAbstractFactory implements AbstractFactoryInterface
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
        if (class_exists($requestedName))
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
        if(strpos($requestedName, '\\Model\\')!==false) {
            $class = $requestedName;
            return new $class($serviceLocator->get('mongos'));
        }
    }
}