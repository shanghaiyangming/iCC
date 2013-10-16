<?php
namespace Application;

return array(
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
            __NAMESPACE__ => dirname(__DIR__) . '/src/' . __NAMESPACE__
        )
    )
);