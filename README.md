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

### 4.2 `module.config.php`

Registra a configuração do módulo para o Zend Framework 2:

### 4.3 `EmailController.php`

Controlador que processa as requisições:

### 4.4 `EmailService.php`

Renderiza o HTML usando o Zend\View:

### 4.5 `EmailControllerFactory.php`

O arquivo `EmailControllerFactory.php` é responsável por **criar e configurar instâncias do controlador `EmailController`**. Ele segue o padrão Factory do Zend Framework 2, garantindo que as dependências necessárias sejam corretamente injetadas no controlador.

#### Detalhes da Função:

1.  **Injeção de Dependência**: O `EmailController` depende do `EmailService`. A factory recupera a instância do serviço do container de serviços do Zend Framework e a injeta no controlador.
2.  **Separação de Responsabilidades**: Permite que a lógica de criação e configuração do controlador fique isolada, tornando o código mais modular e testável.
3.  **Flexibilidade**: Facilita a troca ou alteração de dependências sem modificar diretamente o controlador.

### 4.5 `EmailControllerFactory.php`

### Função do `email_template.phtml`

O arquivo `email_template.phtml` é um **template de visualização**, utilizado para gerar o HTML do e-mail marketing. Ele contém o layout e a estrutura básica do e-mail, que são preenchidos com os dados fornecidos à API.

#### Detalhes da Função:

1.  **Renderização do HTML**: Serve como modelo para criar o conteúdo dinâmico do e-mail, com base nos parâmetros recebidos pelo `EmailService`.
2.  **Flexibilidade Visual**: Permite alterações no design do e-mail sem modificar o código da aplicação.
3.  **Reutilização**: Pode ser facilmente adaptado para outros tipos de e-mails, bastando alterar ou expandir seu conteúdo.

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
