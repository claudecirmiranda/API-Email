<?php

namespace Api;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'api.email' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/email[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\EmailController::class,
                        'action'     => 'generateHtml', // Define a ação padrão
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            \Api\Controller\EmailController::class => \Api\Factory\EmailControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            \Api\Service\EmailService::class => function () {
                return new \Api\Service\EmailService();
            },
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'Api' => __DIR__ . '/../src/View',
        ],
    ],
];
