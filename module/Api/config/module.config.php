<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Api\Controller\Questionnaire' => 'Api\Controller\QuestionnaireController',
        ),
    ),
    'router' => array(
        'routes' => array(
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /api/:controller/:id
            'api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/[:controller[/:id]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Api\Controller',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);