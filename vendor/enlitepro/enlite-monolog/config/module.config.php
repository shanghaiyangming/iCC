<?php

return array(
    'EnliteMonolog' => array(
        // Logger name
        'EnliteMonologService' => array(
            // name of
            'name' => 'default',
            // Handlers, it can be service manager alias(string) or config(array)
            'handlers' => array(
                'default' => array(
                    'name' => 'Monolog\Handler\StreamHandler',
                    'args' => array(
                        'path' => ROOT_PATH.'/data/log/application.log',
                        'level' => \Monolog\Logger::DEBUG,
                        'bubble' => true
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'EnliteMonolog\Service\MonologServiceAbstractFactory'
        ),
        'initializers' => array(
            'EnliteMonolog\Service\MonologServiceInitializer'
        )
    )
);