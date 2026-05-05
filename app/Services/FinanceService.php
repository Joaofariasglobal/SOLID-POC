<?php

namespace App\Services;

use App\Contracts\FinanceRepositoryInterface;
use App\Domain\BaseTransaction;
use App\Domain\ExpenseTransaction;
use App\Domain\IncomeTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use RuntimeException;

class FinanceService implements FinanceRepositoryInterface
{
    private const CATEGORIAS_RECEITA = ['salario', 'freelance', 'investimento', 'outros'];
    private const CATEGORIAS_DESPESA = ['alimentacao', 'transporte', 'moradia', 'lazer', 'saude', 'educacao', 'outros'];

    private const COTACAO_USD = 5.20;
    private const COTACAO_EUR = 5.65;

    public function createUser(array $data): array
    {
        if (! isset($data['name']) || trim($data['name']) === '') {
            throw new InvalidArgumentException('Nome é obrigatório.');
        }

        if (! isset($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }

        if (strlen($data['name']) < 3) {
            throw new InvalidArgumentException('Nome deve ter ao menos 3 caracteres.');
        }

        $existing = DB::table('users')->where('email', $data['email'])->first();
        if ($existing) {
            throw new InvalidArgumentException('E-mail já cadastrado.');
        }

        $id = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("[FinanceService] Usuário criado: id={$id} email={$data['email']}");

        try {
            Mail::raw("Bem-vindo(a), {$data['name']}!", function ($m) use ($data) {
                $m->to($data['email'])->subject('Cadastro realizado');
            });
        } catch (\Throwable $e) {
            Log::warning("[FinanceService] Falha ao enviar e-mail: {$e->getMessage()}");
        }

        return [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
        ];
    }

    public function findUser(int $id): ?array
    {
        $user = User::find($id);
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    public function saveTransaction(array $data): array
    {
        if (! isset($data['user_id'])) {
            throw new InvalidArgumentException('user_id é obrigatório.');
        }
        if (! User::find($data['user_id'])) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }
        if (! isset($data['type']) || ! in_array($data['type'], ['income', 'expense'], true)) {
            throw new InvalidArgumentException('type deve ser income ou expense.');
        }
        if (! isset($data['amount']) || ! is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new InvalidArgumentException('amount deve ser numérico e maior que zero.');
        }
        if (! isset($data['category'])) {
            throw new InvalidArgumentException('category é obrigatório.');
        }

        if ($data['type'] === 'income') {
            if (! in_array($data['category'], self::CATEGORIAS_RECEITA, true)) {
                throw new InvalidArgumentException('Categoria inválida para receita.');
            }
        } elseif ($data['type'] === 'expense') {
            if (! in_array($data['category'], self::CATEGORIAS_DESPESA, true)) {
                throw new InvalidArgumentException('Categoria inválida para despesa.');
            }
        }

        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'BRL';
        if ($currency === 'USD') {
            $amount = $amount * self::COTACAO_USD;
        } elseif ($currency === 'EUR') {
            $amount = $amount * self::COTACAO_EUR;
        } elseif ($currency !== 'BRL') {
            throw new InvalidArgumentException('Moeda não suportada.');
        }

        if ($data['type'] === 'expense' && isset($data['discount'])) {
            $domain = new ExpenseTransaction($amount, $data['description'] ?? '', $data['category']);
            $amount = $domain->applyDiscount((float) $data['discount']);
        } elseif ($data['type'] === 'income' && isset($data['discount'])) {
            $domain = new IncomeTransaction($amount, $data['description'] ?? '', $data['category']);
            $amount = $domain->applyDiscount((float) $data['discount']);
        }

        $occurredAt = isset($data['occurred_at'])
            ? Carbon::parse($data['occurred_at'])
            : Carbon::now();

        $id = DB::table('transactions')->insertGetId([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => $amount,
            'occurred_at' => $occurredAt->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("[FinanceService] Transação salva: id={$id} user={$data['user_id']} type={$data['type']} amount={$amount}");

        if ($data['type'] === 'expense' && $amount > 5000) {
            Log::warning("[FinanceService] ALERTA: despesa alta detectada para user={$data['user_id']}: R$ {$amount}");
        }

        return [
            'id' => $id,
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => number_format($amount, 2, '.', ''),
            'occurred_at' => $occurredAt->toDateString(),
        ];
    }

    public function listTransactions(int $userId): array
    {
        $rows = DB::table('transactions')
            ->where('user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $signedAmount = $row->type === 'income' ? (float) $row->amount : -((float) $row->amount);
            $result[] = [
                'id' => $row->id,
                'type' => $row->type,
                'category' => $row->category,
                'description' => $row->description,
                'amount' => number_format((float) $row->amount, 2, '.', ''),
                'signed_amount' => number_format($signedAmount, 2, '.', ''),
                'occurred_at' => $row->occurred_at,
                'icon' => $row->type === 'income' ? '+' : '-',
                'color' => $row->type === 'income' ? 'green' : 'red',
            ];
        }

        return $result;
    }

    public function getStatement(int $userId): array
    {
        $user = User::find($userId);
        if (! $user) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }

        $rows = DB::table('transactions')->where('user_id', $userId)->get();

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        $porCategoria = [];
        $items = [];

        foreach ($rows as $row) {
            $amount = (float) $row->amount;

            if ($row->type === 'income') {
                $totalReceitas += $amount;
            } elseif ($row->type === 'expense') {
                $totalDespesas += $amount;
            } else {
                throw new RuntimeException("Tipo de transação desconhecido: {$row->type}");
            }

            $key = $row->type . ':' . ($row->category ?? 'outros');
            if (! isset($porCategoria[$key])) {
                $porCategoria[$key] = 0.0;
            }
            $porCategoria[$key] += $amount;

            $items[] = [
                'id' => $row->id,
                'type' => $row->type,
                'category' => $row->category,
                'description' => $row->description,
                'amount' => number_format($amount, 2, '.', ''),
                'signed_amount' => $row->type === 'income'
                    ? number_format($amount, 2, '.', '')
                    : number_format(-$amount, 2, '.', ''),
                'occurred_at' => $row->occurred_at,
                'icon' => $row->type === 'income' ? '+' : '-',
                'color' => $row->type === 'income' ? 'green' : 'red',
            ];
        }

        $saldo = $totalReceitas - $totalDespesas;
        $status = $saldo >= 0 ? 'positivo' : 'negativo';

        if ($saldo < 0) {
            Log::warning("[FinanceService] Usuário {$userId} está com saldo negativo: R$ {$saldo}");
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'totais' => [
                'receitas' => number_format($totalReceitas, 2, '.', ''),
                'despesas' => number_format($totalDespesas, 2, '.', ''),
                'saldo' => number_format($saldo, 2, '.', ''),
                'status' => $status,
            ],
            'por_categoria' => array_map(
                fn ($v) => number_format($v, 2, '.', ''),
                $porCategoria
            ),
            'transacoes' => $items,
            'gerado_em' => now()->toIso8601String(),
        ];
    }

    public function deleteTransaction(int $id): void
    {
        DB::table('transactions')->where('id', $id)->delete();
    }

    public function exportToCsv(int $userId): string
    {
        throw new RuntimeException('Não implementado.');
    }

    public function exportToPdf(int $userId): string
    {
        throw new RuntimeException('Não implementado.');
    }

    public function exportToXml(int $userId): string
    {
        throw new RuntimeException('Não implementado.');
    }

    public function importFromCsv(int $userId, string $content): int
    {
        throw new RuntimeException('Não implementado.');
    }

    public function sendEmailReport(int $userId): bool
    {
        throw new RuntimeException('Não implementado.');
    }

    public function sendSmsReport(int $userId): bool
    {
        throw new RuntimeException('Não implementado.');
    }

    public function calculateIncomeTax(int $userId, int $year): float
    {
        throw new RuntimeException('Não implementado.');
    }

    public function generateMonthlyChart(int $userId): string
    {
        throw new RuntimeException('Não implementado.');
    }

    public function backupDatabase(): string
    {
        throw new RuntimeException('Não implementado.');
    }

    private function transactionFromArray(array $row): BaseTransaction
    {
        if ($row['type'] === 'income') {
            return new IncomeTransaction((float) $row['amount'], $row['description'] ?? '', $row['category'] ?? 'outros');
        } elseif ($row['type'] === 'expense') {
            return new ExpenseTransaction((float) $row['amount'], $row['description'] ?? '', $row['category'] ?? 'outros');
        }
        throw new RuntimeException("Tipo desconhecido: {$row['type']}");
    }
}
