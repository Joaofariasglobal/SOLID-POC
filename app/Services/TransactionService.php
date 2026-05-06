<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\UserRepositoryInterface;
use InvalidArgumentException;

class TransactionService implements TransactionServiceInterface
{
    private TransactionRepositoryInterface $transactionRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserRepositoryInterface $userRepository,
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
    }
    public function saveTransaction(array $data): array
    {
        if (! $this->userRepository->findUser((int) $data['user_id'])) {
        throw new InvalidArgumentException('Usuário não encontrado.');
    }
        
        return $this->transactionRepository->saveTransaction($data);
    }

    public function listTransactions(int $userId): array
    {
        return $this->transactionRepository->listTransactions($userId);
    }

    public function deleteTransaction(int $id): void
    {
        $this->transactionRepository->deleteTransaction($id);
    }
}
