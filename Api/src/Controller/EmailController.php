<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Http\PhpEnvironment\RequestInterface;
use Api\Service\EmailService;

class EmailController extends AbstractActionController
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function generateHtmlAction()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if ($request->isGet()) {
            // Retorna uma documentação simplificada para o endpoint
            return new JsonModel([
                'swagger' => [
                    'method' => 'POST',
                    'endpoint' => '/api/email',
                    'description' => 'Gera um HTML para um e-mail com base nos parâmetros fornecidos.',
                    'parameters' => [
                        'customer' => 'string',
                        'order' => 'string',
                        'order_date' => 'string',
                        'payment_method' => 'string',
                        'delivery_type' => 'string',
                        'delivery_forecast' => 'string',
                        'order_tracking' => 'string',
                        'products' => [
                            [
                                'Product Description' => 'string',
                                'Quantity' => 'int',
                                'Price' => 'string',
                                'Total' => 'string',
                            ],
                        ],
                        'summary' => [
                            'Itens' => 'string',
                            'Warranties' => 'string',
                            'Shipping' => 'string',
                            'Total' => 'string',
                        ],
                    ],
                ],
            ]);
        }

        if ($request->isPost()) {
            $contentType = $request->getHeader('Content-Type')->getFieldValue();
        
            if (strpos($contentType, 'application/json') === false) {
                return new JsonModel(['error' => 'Invalid Content-Type. Expected application/json.']);
            }
        
            $data = json_decode($request->getContent(), true);
        
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonModel(['error' => 'Invalid JSON payload.']);
            }
        
            // Validação dos campos obrigatórios
            $requiredFields = ['customer', 'order', 'products', 'summary'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return new JsonModel(['error' => "Field '$field' is required."]);
                }
            }
        
            
            // Renderiza o template do e-mail
            $html = $this->emailService->renderEmailTemplate($data);

            // Remove caracteres indesejados
            $cleanHtml = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
            $cleanHtml = str_replace(["\r", "\n", "\t"], '', $cleanHtml);
            $cleanHtml = str_replace(['"'], "'", $cleanHtml);            
        
            // Retorna o HTML limpo
            return new JsonModel(['html' => $cleanHtml]);
        }

        // Resposta padrão para outros métodos HTTP
        return new JsonModel(['error' => 'Invalid request method.']);
    }
}
