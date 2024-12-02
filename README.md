### Documentação Técnica: Arquitetura de Solução para API PHP 7 com Zend Framework 2

* * *

1. **Visão Geral**
------------------

A solução é uma API construída em PHP 7 utilizando Zend Framework 2. Ela recebe parâmetros em formato JSON e gera um HTML formatado para campanhas de e-mail marketing. O design modular da aplicação facilita a manutenção e a integração com outros serviços.

* * *

2. **Estrutura do Projeto**
---------------------------

A organização do módulo `Api` está detalhada abaixo:

```bash
nagemcombr/module/Api 
│   Module.php 
│ ├───config 
│       module.config.php 
│ └───src     
├───Controller     
│       EmailController.php     
│     ├───Factory     
│       EmailControllerFactory.php     
│     ├───Service     
│       EmailService.php     
│     ├───util     
│       simple_html_dom.php
│       constants.php
│       Debug.php
│       HtmlDocument.php
│       HtmlNode.php
│       HtmlWeb.php
│     └───View
             email_template.phtml
             email_template.html

```

### Arquivos Principais

1.  **`Module.php`**: Registra a configuração do módulo.
2.  **`module.config.php`**: Define rotas, controladores e configurações de serviços.
3.  **`EmailController.php`**: Controlador que expõe os endpoints da API.
4.  **`EmailService.php`**: Serviço responsável pela renderização do template de e-mail.
5.  **`email_template.phtml`**: Template PHP para o HTML do e-mail.

* * *

3. **Fluxo de Funcionamento**
-----------------------------

1.  **Requisição**: O cliente faz um GET para o endpoint `/api/email` e obtém a estrutura do JSON necessário para a requisição POST.
2.  **Requisição**: O cliente faz um POST para o endpoint `/api/email` enviando um payload JSON.
3.  **Validação**:
    *   O conteúdo é verificado para garantir que seja JSON válido.
    *   Campos obrigatórios são validados.
4.  **Renderização**:
    *   O `EmailService` utiliza o Zend\View\Renderer para renderizar o HTML a partir do template `email_template.phtml`.
5.  **Resposta**:
    *   Retorna o HTML formatado ou mensagens de erro em caso de falha.

* * *

4. **Detalhamento dos Componentes**
-----------------------------------

### 4.1 `Module.php`

Registra a configuração do módulo para o Zend Framework 2:

```php
namespace Api;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}

```

### 4.2 `module.config.php`

Registra a configuração do módulo para o Zend Framework 2:

```php
return [
    'router' => [
        'routes' => [
            'api.email' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/email[/:action]',
                    'constraints' => ['action' => '[a-zA-Z][a-zA-Z0-9_-]*'],
                    'defaults' => [
                        'controller' => Controller\EmailController::class,
                        'action' => 'generateHtml',
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
```

### 4.3 `EmailController.php`

Controlador que processa as requisições:

```php
namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
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
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonModel(['error' => 'Invalid JSON payload.']);
            }

            $html = $this->emailService->renderEmailTemplate($data);
            return new JsonModel(['html' => $html]);
        }

        return new JsonModel(['error' => 'Invalid request method.']);
    }
}
```

### 4.4 `EmailService.php`

Renderiza o HTML usando o Zend\View:

```php
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
        ]);

        $this->renderer = new PhpRenderer();
        $this->renderer->setResolver($resolver);
    }

    public function renderEmailTemplate(array $data)
    {
        return $this->renderer->render('email-template', $data);
    }
}

```

### 4.5 `EmailControllerFactory.php`

O arquivo `EmailControllerFactory.php` é responsável por **criar e configurar instâncias do controlador `EmailController`**. Ele segue o padrão Factory do Zend Framework 2, garantindo que as dependências necessárias sejam corretamente injetadas no controlador.

#### Detalhes da Função:

1.  **Injeção de Dependência**: O `EmailController` depende do `EmailService`. A factory recupera a instância do serviço do container de serviços do Zend Framework e a injeta no controlador.
2.  **Separação de Responsabilidades**: Permite que a lógica de criação e configuração do controlador fique isolada, tornando o código mais modular e testável.
3.  **Flexibilidade**: Facilita a troca ou alteração de dependências sem modificar diretamente o controlador.


```php
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
```

### 4.5 `EmailControllerFactory.php`

### Função do `email_template.phtml`

O arquivo `email_template.phtml` é um **template de visualização**, utilizado para gerar o HTML do e-mail marketing. Ele contém o layout e a estrutura básica do e-mail, que são preenchidos com os dados fornecidos à API.

#### Detalhes da Função:

1.  **Renderização do HTML**: Serve como modelo para criar o conteúdo dinâmico do e-mail, com base nos parâmetros recebidos pelo `EmailService`.
2.  **Flexibilidade Visual**: Permite alterações no design do e-mail sem modificar o código da aplicação.
3.  **Reutilização**: Pode ser facilmente adaptado para outros tipos de e-mails, bastando alterar ou expandir seu conteúdo.

#### Exemplo de Estrutura do `email_template.phtml`:

```html
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' lang='pt-br'>
<head>
    <META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
    <title>Nagem.com.br / <?= htmlspecialchars($this->escapeHtml($subject)) ?></title>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <meta name=viewport content='width=device-width, initial-scale=1' />
    <style type='text/css'>
        body {
            font-family: 'Arial';
        }
    </style>
    <?php
    function formatarValorMonetario(string $valor): string
    {
        // Substitui a vírgula por ponto e converte em número float
        $valorFloat = floatval(str_replace(',', '.', $valor));

        // Formata o número com separadores de milhar e decimal no padrão brasileiro
        return "R$ " . number_format($valorFloat, 2, ',', '.');
    }
    ?>
</head>

<body bgcolor='#f2f2f2' style='text-align:center;'>
    <table border='0' cellpadding='0' cellspacing='0' width='640' align='center' style='background-color:#ffffff; width:620px; margin:0 auto;'>
        <tr style='height: 75px;'>
            <td colspan='3' style='background-color:#0046B4;color:#ffffff;text-align:center;'>
                <table align='center' style='width: 94%; '>
                    <td><img style='float: left: padding 10px' src='http://trunk.nagem.com.br/util/artefatos/asset/n/13281721392427/img/layout/email/logoNagem.png' height='25' /></td>
                    <td style='width: 93px; padding-left: 20px;'>
                        <table>
                            <tr>
                                <td valign='top'>
                                    <p style='font-size: 30px; margin: 5px; font-weight: bold; color: #f4f4f4;'>34</p>
                                </td>
                                <td valign='top' style='text-align: left; width: 60px; font-size: 10px; color: #f4f4f4; padding: 11px 0; line-height: 12px;'>Anos de mercado</td>
                            </tr>
                        </table>
                    </td>
                    <td><img src='http://trunk.nagem.com.br/util/artefatos/asset/n/1121721392427/img/layout/email/img-separador-menu.png' height='25' /></td>
                    <td>
                        <a href='http://trunk.nagem.com.br/institucional/nossaslojas/' target='_blank' style='text-decoration: none;'>
                            <table>
                                <tr>
                                    <td valign='top'>
                                        <p style='font-size: 29px; margin: 5px; font-weight: bold; color: #f4f4f4;'>55</p>
                                    </td>
                                    <td valign='top' style='text-align: left; width: 60px; font-size: 10px; color: #f4f4f4; padding: 11px 0; line-height: 12px;'>Lojas pelo Brasil</td>
                                </tr>
                            </table>
                        </a>
                    </td>
                    <td><img src='http://trunk.nagem.com.br/util/artefatos/asset/n/1121721392427/img/layout/email/img-separador-menu.png' height='25' /></td>
                    <td>
                        <table>
                            <tr>
                                <td valign='top'><img style='padding: 8px 0;' src='http://trunk.nagem.com.br/util/artefatos/asset/n/3611721392427/img/layout/email/ebit-logo.png' height='20'></td>
                                <td valign='top' style='text-align: left; width: 75px; font-size: 10px; color: #f4f4f4; padding: 8px 0; line-height: 12px;'>Loja Avaliada <br />no E-Bit</td>
                            </tr>
                        </table>
                    </td>
                    <td><img src='http://trunk.nagem.com.br/util/artefatos/asset/n/1121721392427/img/layout/email/img-separador-menu.png' height='25'></td>
                    <td>
                        <table>
                            <tr>
                                <td valign='top'><img style='padding: 10px 0;' src='http://trunk.nagem.com.br/util/artefatos/asset/n/3841721392427/img/layout/email/sac.png' /></td>
                                <td valign='top' style='text-align:left; width:65px; font-size:10px; color:#f4f4f4; padding: 9px 0; line-height: 12px;'>SAC Site<br><a href='tel:0800-0802121' style='font-size:10px; color:#f4f4f4; text-decoration: none;'>0800 0802121</a></td>
                            </tr>
                        </table>
                    </td>
                    <td><img src='http://trunk.nagem.com.br/util/artefatos/asset/n/1121721392427/img/layout/email/img-separador-menu.png' height='25'></td>
                    <td>
                        <table>
                            <tr>
                                <td valign='top'><span style='font-size: 18px; font-weight: bold; color: #ffffff;'><img src='http://trunk.nagem.com.br/util/artefatos/asset/n/4011721392427/img/layout/email/cd.png' /></span></td>
                                <td valign='top' style='text-align: left; width: 30px; font-size: 10px; color: #ffffff; line-height: 12px;'><a style='font-size:10px;color:#f4f4f4;text-decoration: none;' target='_blank' href='http://trunk.nagem.com.br/institucional/nossaslojas/'>Lojas <br>e CDs</a></td>
                            </tr>
                        </table>
                    </td>
                </table>
            </td>
        </tr>
        <tr>
        <tr>
            <td>
                <table width=96% border=0 align=center>
                    <td align="left">
                        </p>
                        <b><?= htmlspecialchars($customer) ?></b>
                        <p>Obrigado por comprar na Nagem. Confirmamos seu <b>pedido <?= htmlspecialchars($order) ?></b> realizado em <?= htmlspecialchars($order_date) ?>.</p>
                        <p><b>Forma de Pagamento:</b><br> <?= htmlspecialchars($payment_method) ?></br></p>
                        <p><b><?= htmlspecialchars($delivery_type) ?></b> estará sujeira ao reconhecimento do pagamento a depender da modalidade realizada.</p>
                        <p><b>Previsão de Entrega:</b> <?= htmlspecialchars($delivery_forecast) ?></p>
                        <p><b>Rastreamento:</b> <a href='<?= htmlspecialchars($order_tracking) ?>'>Clique aqui para reastrear seu Pedido</a></p>
                        <p>Caso tenha alguma dúvida contate o Vendedor que fez seu atendimento.</p>
                        <p>Equipe Nagem</p>
                        </p>
                </table>
        </tr>
        </td>
        <tr>
            <td>
                <table width="96%" align="center" style="border-collapse: collapse; margin: 0 auto; background-color: #ffffff;">
                    <thead>
                        <tr style='background-color:#0046B4;color:#ffffff;text-align:left;'>
                            <th>Descrição dos Produtos</th>
                            <th>Qtd.</th>
                            <th>Preço</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody style='background-color:#ffffff;color:#000;text-align:left;'>
                        <tr>
                            <td>
                                </p>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['Product Description']) ?></td>
                                <td><?= htmlspecialchars($product['Quantity']) ?></td>
                                <td><?= formatarValorMonetario(htmlspecialchars($product['Price'])) ?></td>
                                <td><?= formatarValorMonetario(htmlspecialchars($product['Total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td>
                                </p>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot style='background-color:#e7eff6;color:#4b86b4;text-align:left;'>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Itens:</td>
                            <td><?= formatarValorMonetario(htmlspecialchars($summary['Itens'])) ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Garantias:</td>
                            <td><?= formatarValorMonetario(htmlspecialchars($summary['Warranties'])) ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Frete:</td>
                            <td><?= formatarValorMonetario(htmlspecialchars($summary['Shipping'])) ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Total:</td>
                            <td><?= formatarValorMonetario(htmlspecialchars($summary['Total'])) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
```

* * *

5. **Endpoints**
-------------------------

### **GET /api/email**

**Descrição**: Retorna informações sobre a estrutura do JSON necessário para a requisição POST.

#### Exemplo de Uso

Requisição:

```http
GET /api/email HTTP/1.1
Host: exemplo.com
```

#### Resposta

```json
{
    "swagger": {
        "method": "POST",
        "endpoint": "/api/email",
        "description": "Gera um HTML para um e-mail com base nos parâmetros fornecidos.",
        "parameters": {
            "customer": "string",
            "order": "string",
            "order_date": "string",
            "payment_method": "string",
            "delivery_type": "string",
            "delivery_forecast": "string",
            "order_tracking": "string",
            "products": [
                {
                    "Product Description": "string",
                    "Quantity": "int",
                    "Price": "string",
                    "Total": "string"
                }
            ],
            "summary": {
                "Itens": "string",
                "Warranties": "string",
                "Shipping": "string",
                "Total": "string"
            }
        }
    }
}
```

### **POST /api/email**

**Descrição**: Gera um HTML baseado nos parâmetros fornecidos.

#### Exemplo de Requisição

```json
{
    "customer": "nome",
    "order": "12345/24",
    "order_date":"25/11/2024 às 09:30:12h",
    "payment_method": "30/60/90 DIA",
    "delivery_type": "ENTREGA EXPRESSA",
    "delivery_forecast": "10/12/2024",
    "order_tracking": "http://link.rastreio/12345",
    "products": [
        {
            "Product Description": "TV 32 LED FHS S5400",
            "Quantity": 2,
            "Price": "1214,93",
            "Total": "2429,86"
        },
        {
            "Product Description": "Monitor 17''",
            "Quantity": 1,
            "Price": "850,50",
            "Total": "850,50"
        }
    ],
    "summary": {
        "Itens": "3280,36",
        "Warranties": "0",
        "Shipping": "56",
        "Total": "3336,36"
    }
}
```
#### Resposta

```json
{
    "html": "<!DOCTYPE html PUBLIC..."
}
```

#### HTML Renderizado

![image](https://github.com/user-attachments/assets/c241f84a-eba5-42ed-94ce-f7757e484824)

* * *

6. **Benefícios da Arquitetura**
--------------------------------

*   **Modularidade**: Facilita a extensão ou substituição de funcionalidades específicas.
*   **Reutilização**: O serviço de renderização pode ser reaproveitado para outros templates.
*   **Facilidade de Testes**: O Zend Framework 2 oferece suporte a testes unitários e de integração.
*   **Manutenibilidade**: O design segue boas práticas de separação de responsabilidades.
