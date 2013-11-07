<?php
return array(
    'Logs' => array(
        // Logger name
        'LogMongodbService' => array(
            // name of
            'name' => 'default',
            // Handlers, it can be service manager alias(string) or config(array)
            'handlers' => array(
                'default' => array(
                    'name' => 'Monolog\Handler\MongoDBHandler',
                    'args' => array(
                        'mongo' => new \MongoClient("mongodb://127.0.0.1:27017"),
                        'database'=>'logs',
                        'collection'=>'logs'.date("Ymd"),
                        'level' => \Monolog\Logger::DEBUG,
                        'bubble' => true
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Logs\Service\MonologServiceAbstractFactory'
        ),
        'initializers' => array(
            'Logs\Service\MonologServiceInitializer'
        )
    )
);