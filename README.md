# Analyze Inventory

API Laravel para análise de inventário a partir de arquivos CSV. Processa movimentações de entrada/saída e retorna estoque atual, itens com baixo estoque e anomalias (estoque negativo).

## Requisitos

- PHP 8.2+
- Composer
- Laravel 12

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Executando

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`.

## Endpoints

### Health Check

```
GET /api/ping
```

Retorna `pong` para verificar se a API está online.

### Análise de Inventário

```
POST /api/analyze-inventory
Content-Type: multipart/form-data
```

**Parâmetros:**

| Campo      | Tipo | Obrigatório | Descrição                    |
|------------|------|-------------|------------------------------|
| inventory  | file | Sim         | Arquivo CSV ou TXT (máx. 12 MB) |

**Formato do CSV esperado:**

| Coluna      | Descrição                    |
|-------------|------------------------------|
| timestamp   | Data/hora (numérico)         |
| product_id  | ID do produto                |
| product_name| Nome do produto              |
| quantity    | Quantidade (numérico)        |
| type        | `in` (entrada) ou `out` (saída) |

**Exemplo de resposta (200):**

```json
{
  "stock": [
    {
      "product_id": "1",
      "product_name": "Produto A",
      "quantity": 50
    }
  ],
  "low_stock": [
    {
      "product_id": "2",
      "product_name": "Produto B",
      "quantity": 5
    }
  ],
  "anomalies": [
    {
      "product_id": "3",
      "product_name": "Produto C",
      "message": "Stock went negative"
    }
  ]
}
```

**Regras:**

- `stock`: saldo atual de todos os produtos
- `low_stock`: produtos com quantidade < 10
- `anomalies`: produtos que ficaram com estoque negativo em algum momento

## Testando com cURL

```bash
curl -X POST http://localhost:8000/api/analyze-inventory \
  -F "inventory=@seu_arquivo.csv" \
  -H "Accept: application/json"
```

## Estrutura do Projeto

```
app/
├── Http/Controllers/
│   └── AnalyzeInventoryController.php   # Validação e orquestração
└── Services/
    └── InventoryAnalyzerService.php    # Lógica de análise do CSV
```

## Licença

MIT
