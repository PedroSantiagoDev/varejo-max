# Varejo Max

Um sistema de gerenciamento de vendas, clientes e produtos.

## Tecnologias Utilizadas

*   PHP
*   Laravel
*   Filament
*   Pest
*   Tailwind CSS
*   Vite

## Instalação

1.  Clone o repositório:
    ```bash
    git clone https://github.com/seu-usuario/varejo-max.git
    cd varejo-max
    ```

2.  Instale as dependências do Composer:
    ```bash
    composer install
    ```

3.  Instale as dependências do NPM:
    ```bash
    npm install
    ```

4.  Copie o arquivo de ambiente de exemplo e configure suas variáveis de ambiente:
    ```bash
    cp .env.example .env
    ```
    Em seguida, configure seu arquivo `.env` com as credenciais do banco de dados e outras configurações necessárias.

5.  Gere a chave da aplicação:
    ```bash
    php artisan key:generate
    ```

6.  Execute as migrações e semeie o banco de dados:
    ```bash
    php artisan migrate --seed
    ```

## Executando a Aplicação

1.  Inicie o servidor de desenvolvimento do Laravel:
    ```bash
    php artisan serve
    ```

2.  Em um terminal separado, inicie o servidor de desenvolvimento do Vite:
    ```bash
    npm run dev
    ```

Acesse a aplicação em [http://localhost:8000](http://localhost:8000).

## Executando os Testes

Para executar a suíte de testes, utilize o seguinte comando:

```bash
php artisan test
```

## Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir uma issue ou enviar um pull request.