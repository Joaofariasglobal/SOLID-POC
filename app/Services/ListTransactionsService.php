<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Presenters\TransactionPresenter;

class ListTransactionsService
{
    public function __construct(
        private TransactionRepositoryInterface $repository,
        private TransactionPresenter $presenter,
    ) {
    }

    public function execute(int $userId): array
    {
        return array_map(
            fn ($tx) => $this->presenter->toArray($tx),
            $this->repository->listTransactions($userId)
        );
    }
}