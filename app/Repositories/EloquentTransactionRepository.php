<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\TransactionFactoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    private TransactionFactoryInterface $transactionFactory;

    public function __construct(TransactionFactoryInterface $transactionFactory)
    {
        $this->transactionFactory = $transactionFactory;
    }

    public function saveTransaction(int $userId,
        string $type,
        string $category,
        ?string $description,
        float $amount,
        Carbon $occurredAt,
        ): int
    {
        $id = DB::table('transactions')->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'occurred_at' => $occurredAt->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function listTransactions(int $userId): array
    {
        $rows = DB::table('transactions')
            ->where('user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $transaction = $this->transactionFactory->createTransaction([
                'type' => $row->type,
                'amount' => (float) $row->amount,
                'description' => $row->description ?? '',
                'category' => $row->category,
            ]);

            $result[] = [
                'id' => $row->id,
                'type' => $row->type,
                'category' => $row->category,
                'description' => $row->description,
                'amount' => (float)$row->amount,
                'signed_amount' => $transaction->getSignedAmount(),
                'occurred_at' => $row->occurred_at,
            ];
        }

        return $result;
    }
    

    public function deleteTransaction(int $id): void
    {
        DB::table('transactions')->where('id', $id)->delete();
    }
}