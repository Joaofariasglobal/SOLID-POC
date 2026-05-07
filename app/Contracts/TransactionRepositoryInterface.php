<?php

namespace App\Contracts;

use App\Domain\BaseTransaction;

interface TransactionRepositoryInterface
{
    public function saveTransaction(array $data): array;

    /** @return array<BaseTransaction> */
    public function listTransactions(int $userId): array;

    public function deleteTransaction(int $id): void;

    /** @return array<BaseTransaction> */
    public function findByUser(int $userId): \Illuminate\Support\Collection;
}