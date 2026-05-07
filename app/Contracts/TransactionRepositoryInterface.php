<?php

namespace App\Contracts;

interface TransactionRepositoryInterface
{    
    public function saveTransaction(int $userId, string $type, string $category, ?string $description, float $amount, \Illuminate\Support\Carbon $occurredAt): int;

    public function listTransactions(int $userId): array;

    public function deleteTransaction(int $id): void;
}