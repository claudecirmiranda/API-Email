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

    public function getTemplateStructureAction()
    {

        $templatePath = __DIR__ . '/../View/email_template.html';

        // Lê o conteúdo do arquivo HTML
        $htmlContent = file_get_contents($templatePath);

        // Utiliza o PHP Simple HTML DOM Parser para analisar o HTML
        require_once('/srv/www/htdocs/myproject/module/Api/util/simple_html_dom.php');
        // Observação: Esta biblioteca requer instalação e configuração adicionais.
        $html = str_get_html($htmlContent);

        // Encontra a lista de produtos
        $collect = $html->find('#collect', 0);

        // Array para armazenar os produtos
        $products = [];

        if ($collect) {
            // Itera sobre os filhos <td> do nó <tr>
            foreach ($collect->find('th') as $td) {
                // Obtém o conteúdo de cada <td>
                $content = $td->innertext;
                $products[] = $content;
            }
        }

        // Expressão regular para encontrar campos delimitados por | fora da lista de produtos
        $regex = '/\|(.*?)\|/';
        preg_match_all($regex, $htmlContent, $matches);

        // Cria a estrutura do JSON
        $campos = [];
        foreach ($matches[1] as $campo) {
            $campos[$campo] = "string"; // Define o tipo padrão como string
        }

        if (!empty($products)) {
            // Adiciona a lista de produtos ao JSON
            $campos['collect'] = $products;
        }

        // Retorna o JSON com a estrutura dos campos
        return new JsonModel($campos);
    }

    public function postTemplateAction()
    {
        // Caminho do template HTML
        $templatePath = __DIR__ . '/../View/email_template.html';

        // Lê o conteúdo do arquivo HTML
        $htmlContent = file_get_contents($templatePath);

        // Obtém os dados enviados via POST (JSON)
        $request = $this->getRequest();
        $postData = json_decode($request->getContent(), true);

        if (!$postData) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Dados inválidos ou formato de JSON incorreto.'
            ]);
        }

        // Substitui os campos delimitados por |campo|
        foreach ($postData as $key => $value) {
            if (!is_array($value)) {
                // Substitui os campos simples
                $htmlContent = str_replace("|$key|", $value, $htmlContent);
            }
        }

        // Substitui os produtos, caso existam
        if (isset($postData['collect']) && is_array($postData['collect'])) {
            $collectHtml = '';
            foreach ($postData['collect'] as $item) {
                $collectHtml .= "<tr>";
                foreach ($item as $key => $value) {
                    $collectHtml .= "<td>" . htmlspecialchars($value) . "</td>";
                }
                $collectHtml .= "</tr>";
            }

            $collectHtml = "<tbody style='background-color:#ffffff;color:#000;text-align:left;'>" . $collectHtml . "</tbody>";

            // Agora, substituímos <to_replace> pelo conteúdo gerado no HTML.
            $htmlContent = str_replace('<to_replace>', $collectHtml, $htmlContent);
        }

        // Remove caracteres indesejados
        $cleanHtml = html_entity_decode($htmlContent, ENT_QUOTES, 'UTF-8');
        $cleanHtml = str_replace(["\r", "\n", "\t"], '', $cleanHtml);
        $cleanHtml = str_replace(['"'], "'", $cleanHtml);

        // Retorna o HTML modificado
        return new JsonModel([
            'status' => 'success',
            'html' => $cleanHtml
        ]);
    }
}
