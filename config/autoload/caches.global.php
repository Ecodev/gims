<?php

return [
    'caches' => [
        'Cache\Computing' => [
            'adapter' => 'Application\Service\Calculator\Cache\Redis',
            'options' => [
                'namespace' => 'gims',
                'server' => [
                    'host' => 'localhost',
                    'port' => '6379',
                ],
                'lib_options' => [
                    \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
                ],
            ],
        ],
    ],
];
