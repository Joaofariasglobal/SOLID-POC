<?php

namespace App\Contracts;

interface TransactionRepositoryInterface
{
    public function saveTransaction(array $data): array;

    public function listTransactions(int $userId): array;

    public function deleteTransaction(int $id): void;

    public function findByUser(int $userId): \Illuminate\Support\Collection;
}