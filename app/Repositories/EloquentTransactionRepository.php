<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Domain\BaseTransaction;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function saveTransaction(array $data): array
    {
        if (isset($data['occurred_at'])) {
            $occurredAt = Carbon::parse($data['occurred_at']);
        } else {
            $occurredAt = Carbon::now();
        }

        $transaction = Transaction::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'occurred_at' => $occurredAt->toDateString(),
        ]);

        return [
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'type' => $transaction->type,
            'category' => $transaction->category,
            'description' => $transaction->description,
            'amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'occurred_at' => $occurredAt->toDateString(),
        ];
    }

    public function listTransactions(int $userId): array
    {
            $rows = Transaction::where('user_id', $userId)
                ->orderBy('occurred_at', 'desc')
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $tx = BaseTransaction::fromArray((array) $row);
                $result[] = [
                    'id' => $row->id,
                    'type' => $row->type,
                    'category' => $row->category,
                    'description' => $row->description,
                    'amount' => number_format((float) $row->amount, 2, '.', ''),
                    'signed_amount' => number_format($tx->getSignedAmount(), 2, '.', ''),
                    'occurred_at' => $row->occurred_at,
                    'icon' => $tx->getIcon(),
                    'color' => $tx->getColor(),
                ];
            }

        return $result;
    }

    public function deleteTransaction(int $id): void
    {
        Transaction::where('id', $id)->delete();
    }

    public function findByUser(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)->get();
    }
}