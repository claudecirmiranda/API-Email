<?php

namespace Api\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Api\Controller\EmailController;
use Api\Service\EmailService;

class EmailControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $emailService = $container->get(EmailService::class);
        return new EmailController($emailService);
    }
}
