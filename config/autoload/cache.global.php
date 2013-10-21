<?php
$memcacheServers = array(
    array(
        '127.0.0.1',
        11211
    ),
    array(
        '127.0.0.1',
        11211
    )
);
return array(
    'caches' => array(
        'coreCache' => array(
            'adapter' => array(
                'name' => 'filesystem'
            ),
            'options' => array(
                'readable' => true,
                'writable' => true,
                'ttl' => 3600
            )
        )
        ,
        'pageCache' => array(
            'adapter' => array(
                'name' => 'filesystem'
            ),
            'options' => array(
                'readable' => true,
                'writable' => true,
                'ttl' => 86400
            )
        )
    )
);