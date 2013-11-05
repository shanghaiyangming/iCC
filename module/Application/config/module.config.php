<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            'application' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'Wildcard' => array(
                                'type' => 'Zend\Mvc\Router\Http\Wildcard',
                                'may_terminate' => true
                            )
                        )
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'My\Common\MongoAbstractFactory',
//             'My\Common\Monolog',
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    'caches' => array(
        'fileCache' => array(
            'adapter' => array(
                'name' => 'filesystem'
            ),
            'options' => array(
                'cache_dir' => ROOT_PATH . '/data/cache/datas'
            )
        ),
        'memcachedCache' => array(
            'adapter' => array(
                'name' => 'memcached'
            ),
            'options' => array(
                'servers' => array(
                    array(
                        '127.0.0.1',
                        11211
                    )
                )
            )
        ),
        'redisCache' => array(
            'adapter' => array(
                'name' => 'redis'
            ),
            'options' => array(
                'servers' => array(
                    array(
                        '127.0.0.1',
                        6379
                    )
                )
            )
        )
    ),
    'mongos' => array(
        'cluster' => array(
            'default' => array(
                'servers' => array(
                    '127.0.0.1:27017',
                    '127.0.0.1:27017'
                ),
                'dbs' => array(
                    'ICCv1',
                    'admin'
                )
            ),
            'analysis' => array(
                'servers' => array(
                    '127.0.0.1:27017',
                    '127.0.0.1:27017'
                ),
                'dbs' => array(
                    'ICCv1',
                    'admin'
                )
            )
        )
    ),
    'monolog'=>array(
        
    ),
    'controllers' => array(
        'invokables' => array()
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy'
        )
    )
);
