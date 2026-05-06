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
        if (! isset($data['user_id'])) {
            throw new InvalidArgumentException('user_id é obrigatório.');
        }
        if (! $this->userRepository->findUser((int) $data['user_id'])) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }
        if (! isset($data['type']) || ! in_array($data['type'], ['income', 'expense'], true)) {
            throw new InvalidArgumentException('type deve ser income ou expense.');
        }
        if (! isset($data['amount']) || ! is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new InvalidArgumentException('amount deve ser numérico e maior que zero.');
        }
        if (! isset($data['category'])) {
            throw new InvalidArgumentException('category é obrigatório.');
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
