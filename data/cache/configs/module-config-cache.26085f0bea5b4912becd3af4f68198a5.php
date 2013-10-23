<?php
return array (
  'router' => 
  array (
    'routes' => 
    array (
      'home' => 
      array (
        'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
        'options' => 
        array (
          'route' => '/',
          'defaults' => 
          array (
            'controller' => 'Application\\Controller\\Index',
            'action' => 'index',
          ),
        ),
      ),
      'application' => 
      array (
        'type' => 'Literal',
        'options' => 
        array (
          'route' => '/application',
          'defaults' => 
          array (
            '__NAMESPACE__' => 'Application\\Controller',
            'controller' => 'Index',
            'action' => 'index',
          ),
        ),
        'may_terminate' => true,
        'child_routes' => 
        array (
          'default' => 
          array (
            'type' => 'Zend\\Mvc\\Router\\Http\\Segment',
            'options' => 
            array (
              'route' => '/[:controller[/:action]]',
              'constraints' => 
              array (
                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
              ),
              'defaults' => 
              array (
              ),
            ),
            'may_terminate' => true,
            'child_routes' => 
            array (
              'Wildcard' => 
              array (
                'type' => 'Zend\\Mvc\\Router\\Http\\Wildcard',
                'may_terminate' => true,
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'service_manager' => 
  array (
    'factories' => 
    array (
      'translator' => 'Zend\\I18n\\Translator\\TranslatorServiceFactory',
      'cache' => 'Zend\\Cache\\Service\\StorageCacheFactory',
    ),
  ),
  'translator' => 
  array (
    'locale' => 'en_US',
    'translation_file_patterns' => 
    array (
      0 => 
      array (
        'type' => 'gettext',
        'base_dir' => 'D:\\MyCode\\iCC\\module\\Application\\config/../language',
        'pattern' => '%s.mo',
      ),
    ),
  ),
  'controllers' => 
  array (
    'invokables' => 
    array (
    ),
  ),
  'view_manager' => 
  array (
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_map' => 
    array (
      'layout/layout' => 'D:\\MyCode\\iCC\\module\\Application\\config/../view/layout/layout.phtml',
      'application/index/index' => 'D:\\MyCode\\iCC\\module\\Application\\config/../view/application/index/index.phtml',
      'error/404' => 'D:\\MyCode\\iCC\\module\\Application\\config/../view/error/404.phtml',
      'error/index' => 'D:\\MyCode\\iCC\\module\\Application\\config/../view/error/index.phtml',
    ),
    'template_path_stack' => 
    array (
      0 => 'D:\\MyCode\\iCC\\module\\Application\\config/../view',
    ),
  ),
  'cache' => 
  array (
    'adapter' => 
    array (
      'name' => 'filesystem',
    ),
    'options' => 
    array (
      'cache_dir' => 'D:\\MyCode\\iCC/data/cache/datas',
    ),
  ),
  'mongos' => 
  array (
    0 => 'mongodb://127.0.0.1:27017',
  ),
);