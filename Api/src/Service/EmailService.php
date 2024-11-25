<?php

namespace Api\Service;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

class EmailService
{
    protected $renderer;

    public function __construct()
    {
        $resolver = new TemplateMapResolver();
        $resolver->setMap([
            'email-template' => __DIR__ . '/../View/email_template.phtml',
            //'email-template' => realpath('layouts/padrao/emails/mensagem-template.php'),
        ]);

        $this->renderer = new PhpRenderer();
        $this->renderer->setResolver($resolver);
    }

    /**
     * Renderiza o template de e-mail.
     */
    public function renderEmailTemplate(array $data)
    {
        return $this->renderer->render('email-template', $data);
    }
}
