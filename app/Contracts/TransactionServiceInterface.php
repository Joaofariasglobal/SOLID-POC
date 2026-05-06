<?php

namespace App\Contracts;

interface TransactionServiceInterface
{
    public function saveTransaction(array $data): array;

    public function listTransactions(int $userId): array;

    public function deleteTransaction(int $id): void;
}
