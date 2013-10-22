<?php
return array(
    'caches' => array(
        'coreCache' => array(
            'adapter' => array(
                'name' => 'filesystem'
            ),
            'options' => array(
                'cache_dir' => dirname(dirname(__DIR__)) . '/data/cache/datas',
                'ttl' => 3600
            )
        ),
        'pageCache' => array(
            'adapter' => array(
                'name' => 'filesystem'
            ),
            'options' => array(
                'cache_dir' => dirname(dirname(__DIR__)) . '/data/cache/datas',
                'ttl' => 86400
            )
        )
    )
);