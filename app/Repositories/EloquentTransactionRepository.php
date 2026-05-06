<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Domain\ExpenseTransaction;
use App\Domain\IncomeTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    private const COTACAO_USD = 5.20;
    private const COTACAO_EUR = 5.65;
    public function saveTransaction(array $data) : array
    {
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'BRL'; 
    
        $amount = match($currency) {
            'USD' => $amount * self::COTACAO_USD,
            'EUR' => $amount * self::COTACAO_EUR,
            'BRL' => $amount,
    };

        $occurredAt = isset($data['occurred_at'])
            ? Carbon::parse($data['occurred_at'])
            : Carbon::now();

        if ($data['type'] === 'expense') {
            $expense = new ExpenseTransaction(
                amount: $amount,
                description: (string) ($data['description'] ?? ''),
                category: (string) $data['category'],
            );

            if (isset($data['discount']) && is_numeric($data['discount'])) {
                $amount = $expense->applyDiscount((float) $data['discount']);
            }
        } elseif ($data['type'] === 'income') {
            new IncomeTransaction(
                amount: $amount,
                description: (string) ($data['description'] ?? ''),
                category: (string) $data['category'],
            );
        } else {
            throw new InvalidArgumentException('Tipo de transação inválido.');
        }

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

        Log::info("[TransactionRepository] Transação salva: id={$id} user={$data['user_id']} type={$data['type']} amount={$amount}");

        if ($data['type'] === 'expense' && $amount > 5000) {
            Log::warning("[TransactionRepository] ALERTA: despesa alta detectada para user={$data['user_id']}: R$ {$amount}");
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
            if ($row->type === 'income') {
                $transaction = new IncomeTransaction(
                    amount: (float) $row->amount,
                    description: (string) ($row->description ?? ''),
                    category: (string) $row->category,
                );
            } else {
                $transaction = new ExpenseTransaction(
                    amount: (float) $row->amount,
                    description: (string) ($row->description ?? ''),
                    category: (string) $row->category,
                );
            }

            $signedAmount = $transaction->getSignedAmount();

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

    public function deleteTransaction(int $id): void
    {
        DB::table('transactions')->where('id', $id)->delete();
    }
}