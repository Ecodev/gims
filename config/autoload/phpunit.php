<?php

return [
    'service_manager' => [
        'factories' => [
            \Application\Service\FakeIdentityProvider::class => function () {
                return new \Application\Service\FakeIdentityProvider();
            },
        ],
    ],
    'zfc_rbac' => [
        'identity_provider' => \Application\Service\FakeIdentityProvider::class,
    ],
    'Calculator\Cache' => [
        'enabled' => false,
    ],
];
