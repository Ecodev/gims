<?php

return [
    'service_manager' => [
        'factories' => [
            'Application\Service\FakeIdentityProvider' => function () {
                return new \Application\Service\FakeIdentityProvider();
            },
        ],
    ],
    'zfc_rbac' => [
        'identity_provider' => 'Application\Service\FakeIdentityProvider',
    ],
    'Calculator\Cache' => [
        'enabled' => false,
    ],
];
