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
            // Nova rota para getTemplateStructureAction
            'api.email.getstructure' => [ 
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/email/getstructure', // Rota específica
                    'defaults' => [
                        'controller' => Controller\EmailController::class,
                        'action' => 'getTemplateStructure',
                    ],
                ],
            ],    
            //postTemplateAction        
            'api.email.structure' => [ 
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/email/repl', // Rota específica
                    'defaults' => [
                        'controller' => Controller\EmailController::class,
                        'action' => 'postTemplate',
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
