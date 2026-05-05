# SOLID-POC — API de Controle Financeiro

API REST em **Laravel 13 / PHP 8.4** com banco **SQLite**, criada como
exercício didático para o bootcamp. O usuário cadastra receitas e despesas
e consulta o extrato consolidado.

> **Atenção:** este código foi escrito propositalmente com **violações dos
> princípios SOLID**. O objetivo do exercício é que o aluno identifique e
> refatore essas violações. Toda a regra de negócio está concentrada em
> uma única classe — `app/Services/FinanceService.php`.

---

## Como rodar com Docker

Pré-requisitos: Docker e Docker Compose instalados.

```bash
# 1. Subir o container (faz build, instala dependências, roda migrations)
docker compose up --build

# Em outro terminal, derrubar quando quiser:
docker compose down

# Para zerar o banco SQLite (apaga o volume):
docker compose down -v
```

A API ficará disponível em **http://localhost:8080** (a porta 8080 está mapeada para a 8000 dentro do container).

### Comandos úteis dentro do container

```bash
# Abrir um shell no container em execução
docker compose exec app bash

# Rodar migrations manualmente (do zero)
docker compose exec app php artisan migrate:fresh

# Rodar tinker
docker compose exec app php artisan tinker
```

---

## Endpoints

Base URL: `http://localhost:8080/api`

### Usuários

| Método | Rota                  | Descrição                |
| ------ | --------------------- | ------------------------ |
| POST   | `/users`              | Cria um usuário          |
| GET    | `/users/{id}`         | Retorna um usuário       |

**POST `/users`**
```json
{
  "name": "Maria Silva",
  "email": "maria@example.com"
}
```

### Transações (receitas e despesas)

| Método | Rota                              | Descrição                |
| ------ | --------------------------------- | ------------------------ |
| POST   | `/users/{id}/transactions`        | Cria receita ou despesa  |
| GET    | `/users/{id}/transactions`        | Lista transações do user |

**POST `/users/{id}/transactions`** (receita)
```json
{
  "type": "income",
  "category": "salario",
  "description": "Salário de maio",
  "amount": 5000.00,
  "occurred_at": "2026-05-05"
}
```

**POST `/users/{id}/transactions`** (despesa)
```json
{
  "type": "expense",
  "category": "alimentacao",
  "description": "Mercado",
  "amount": 350.75,
  "occurred_at": "2026-05-04"
}
```

Categorias aceitas:
- **Receita:** `salario`, `freelance`, `investimento`, `outros`
- **Despesa:** `alimentacao`, `transporte`, `moradia`, `lazer`, `saude`, `educacao`, `outros`

Campos opcionais: `currency` (`BRL`, `USD`, `EUR`), `discount` (percentual).

### Extrato

| Método | Rota                       | Descrição                                     |
| ------ | -------------------------- | --------------------------------------------- |
| GET    | `/users/{id}/statement`    | Retorna saldo, totais e lista de transações   |

Resposta:
```json
{
  "user": { "id": 1, "name": "...", "email": "..." },
  "totais": {
    "receitas": "5000.00",
    "despesas": "350.75",
    "saldo": "4649.25",
    "status": "positivo"
  },
  "por_categoria": { "income:salario": "5000.00", "expense:alimentacao": "350.75" },
  "transacoes": [ ... ],
  "gerado_em": "2026-05-05T..."
}
```

### Exemplo rápido com `curl`

```bash
# Criar usuário
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Maria Silva","email":"maria@example.com"}'

# Adicionar receita
curl -X POST http://localhost:8080/api/users/1/transactions \
  -H "Content-Type: application/json" \
  -d '{"type":"income","category":"salario","amount":5000,"description":"Salário"}'

# Adicionar despesa
curl -X POST http://localhost:8080/api/users/1/transactions \
  -H "Content-Type: application/json" \
  -d '{"type":"expense","category":"alimentacao","amount":350.75,"description":"Mercado"}'

# Consultar extrato
curl http://localhost:8080/api/users/1/statement
```

---

## O desafio: encontre e corrija as violações SOLID

O código atual funciona, mas é um péssimo exemplo de design. Sua missão é
**refatorá-lo aplicando os cinco princípios SOLID**:

- **S** — Single Responsibility Principle
- **O** — Open/Closed Principle
- **L** — Liskov Substitution Principle
- **I** — Interface Segregation Principle
- **D** — Dependency Inversion Principle

### Roteiro sugerido

1. Leia `app/Services/FinanceService.php` por inteiro. Liste tudo o que ele
   faz. Quantas razões diferentes existem para esse arquivo mudar?
2. Olhe os controllers em `app/Http/Controllers/`. Como eles obtêm a
   instância do service? Isso facilita ou dificulta testes unitários?
3. Procure por estruturas `if/elseif` ou `in_array` que decidem o que fazer
   com base no **tipo** da transação. O que acontece se amanhã for preciso
   adicionar um tipo `investment` ou `transfer`?
4. Examine `app/Domain/`: a hierarquia de `BaseTransaction` é segura?
   Substituir uma instância da classe-mãe por uma das filhas mantém o
   contrato esperado?
5. Abra `app/Contracts/FinanceRepositoryInterface.php`. Os clientes dessa
   interface usam todos os métodos? O que acontece quando alguém precisa
   implementar só uma parte?

### Critérios de pronto

A refatoração está concluída quando:

- [ ] Cada classe tem **uma única responsabilidade** clara, expressa pelo seu nome.
- [ ] Adicionar um novo tipo de transação **não exige editar** as classes existentes.
- [ ] Subclasses de `BaseTransaction` podem ser usadas onde a base é esperada **sem quebrar** o programa.
- [ ] Interfaces são **pequenas e coesas** — nenhum implementador é forçado a depender de métodos que não usa.
- [ ] Controllers e serviços **dependem de abstrações** (injetadas via construtor), não de classes concretas instanciadas com `new` ou de facades estáticas.
- [ ] Os endpoints continuam funcionando exatamente como antes (a API é o contrato; só a estrutura interna muda).

> Dica: comece pelo SRP. Quebrar o `FinanceService` em peças menores
> (validador, repositório, calculadora de extrato, formatador) já
> destrava a aplicação dos demais princípios.

---

## Gabarito (somente para o instrutor)

<details>
<summary>Spoiler — clique para expandir</summary>

### Violações plantadas

| # | Princípio | Onde                                                                              | O que está errado                                                                                                                |
| - | --------- | --------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| 1 | **SRP**   | `FinanceService`                                                                  | Faz validação, persistência (DB facade), regra de negócio, conversão de moeda, formatação de saída, logging e envio de e-mail.   |
| 2 | **OCP**   | `FinanceService::saveTransaction`, `getStatement`, `listTransactions`             | Cascatas de `if ($type === 'income') ... elseif ($type === 'expense')`. Adicionar um novo tipo exige editar todos os métodos.    |
| 3 | **LSP**   | `ExpenseTransaction::applyDiscount`                                               | Lança `LogicException` em método herdado da base — quebra o contrato de substituibilidade.                                       |
| 4 | **ISP**   | `FinanceRepositoryInterface`                                                      | Interface "gorda" com 14 métodos (CSV, PDF, XML, SMS, IR, gráficos, backup). O service implementa só alguns; o resto lança exceção. |
| 5 | **DIP**   | Controllers fazem `new FinanceService()`; service usa `DB::`, `Log::`, `Mail::`   | Dependências concretas e estáticas. Impossível testar isoladamente sem subir o Laravel inteiro.                                  |

### Caminho de refatoração sugerido

1. **Extrair classes do `FinanceService`:**
   - `UserValidator` (ou FormRequests do Laravel)
   - `TransactionValidator`
   - `UserRepository` (interface) e `EloquentUserRepository`
   - `TransactionRepository` (interface) e `EloquentTransactionRepository`
   - `StatementCalculator` (saldo, totais, agrupamento por categoria)
   - `CurrencyConverter` (interface) com implementação `FixedRateCurrencyConverter` (e abre porta para uma `ApiCurrencyConverter` no futuro — OCP).
2. **Resolver OCP nas transações:** introduzir uma interface `TransactionType` com método `signedAmount(): float` (Strategy/polimorfismo). Cada tipo (`Income`, `Expense`, futuros `Investment`, `Transfer`) implementa por conta própria — sem `if`.
3. **Resolver LSP:** retirar `applyDiscount` da base, ou movê-lo para uma interface `Discountable` que apenas tipos que aceitam desconto implementam. Substituibilidade preservada.
4. **Resolver ISP:** quebrar a interface gorda em pequenas — `UserRepository`, `TransactionRepository`, `StatementExporter`, `ReportNotifier` etc. Cada uma com 1–3 métodos coesos.
5. **Resolver DIP:** registrar bindings no `AppServiceProvider`. Controllers recebem dependências via construtor (`__construct(StatementCalculator $calc, ...)`) — o Service Container do Laravel resolve. Substitua `DB::`, `Log::`, `Mail::` por interfaces injetadas (`LoggerInterface`, etc.).

### Ordem didática recomendada de aulas

1. SRP — extrair validação e persistência.
2. DIP — introduzir DI via construtor; eliminar `new` nos controllers.
3. OCP — substituir `if` por polimorfismo de `TransactionType`.
4. LSP — corrigir hierarquia.
5. ISP — quebrar a interface gorda.

</details>

---

## Estrutura relevante do projeto

```
app/
├── Contracts/
│   └── FinanceRepositoryInterface.php   # interface "gorda" (ISP)
├── Domain/
│   ├── BaseTransaction.php              # classe abstrata
│   ├── IncomeTransaction.php
│   └── ExpenseTransaction.php           # quebra LSP
├── Http/Controllers/
│   ├── UserController.php               # acoplado ao service (DIP)
│   ├── TransactionController.php
│   └── StatementController.php
├── Models/
│   ├── User.php
│   └── Transaction.php
└── Services/
    └── FinanceService.php               # tudo aqui dentro (SRP, OCP)

database/migrations/
├── 0001_01_01_000000_create_users_table.php
└── 2026_05_05_000001_create_transactions_table.php

routes/api.php
Dockerfile
docker-compose.yml
```

---

## Stack

- PHP 8.4
- Laravel 13
- SQLite (PDO)
- Docker + Docker Compose
