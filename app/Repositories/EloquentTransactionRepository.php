<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\TransactionFactoryInterface;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(private TransactionFactoryInterface $factory) {}

    public function saveTransaction(array $data): array
    {
        $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : Carbon::now();

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
        return $this->fetchDomain($userId);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->fetchDomain($userId);
    }

    public function deleteTransaction(int $id): void
    {
        Transaction::where('id', $id)->delete();
    }

    /** @return array<\App\Domain\BaseTransaction> */
    private function fetchDomain(int $userId): array
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get()
            ->map(fn ($row) => $this->factory->fromArray([
                'id' => $row->id,
                'type' => $row->type,
                'category' => $row->category,
                'description' => $row->description,
                'amount' => $row->amount,
                'occurred_at' => $row->occurred_at,
            ]))
            ->all();
    }
}