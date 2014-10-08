<?php

return [
    'service_manager' => [
        'factories' => [
            'ApplicationTest\Service\FakeIdentityProvider' => function() {
                return new \ApplicationTest\Service\FakeIdentityProvider();
            }
        ],
    ],
    'zfc_rbac' => [
        'identity_provider' => 'ApplicationTest\Service\FakeIdentityProvider'
    ],
    'caches' => [
        'Cache\Computing' => [
            'adapter' => 'Application\Service\Calculator\Cache\Blackhole',
            'options' => null
        ],
    ],
];
