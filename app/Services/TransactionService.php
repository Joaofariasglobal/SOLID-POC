<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\Contracts\TransactionFactoryInterface;
use App\Domain\ExpenseTransaction;
use App\Events\HighExpenseDetected;
use App\Events\TransactionCreated;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Illuminate\Support\Carbon;

class TransactionService implements TransactionServiceInterface
{
    private TransactionFactoryInterface $transactionFactory;
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private TransactionRepositoryInterface $transactionRepository;
    private UserRepositoryInterface $userRepository;
    private float $highExpenseThreshold;

    public function __construct(
        TransactionFactoryInterface $transactionFactory,
        ExchangeRateProviderInterface $exchangeRateProvider,
        TransactionRepositoryInterface $transactionRepository,
        UserRepositoryInterface $userRepository,
        float $highExpenseThreshold
    )
    {
        $this->transactionFactory = $transactionFactory;
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
        $this->highExpenseThreshold = $highExpenseThreshold;
    }
    public function saveTransaction(array $data): array
    {
        if (! $this->userRepository->findUser($data['user_id'])) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'BRL';
        $amountInBRL = $this->exchangeRateProvider->convertToBrl($amount, $currency);
        
        if ($data['type'] === 'expense' && isset($data['discount']) && is_numeric($data['discount'])) {
            $expense = $this->transactionFactory->createTransaction([
                'type' => 'expense',
                'amount' => $amountInBRL,
                'description' => ($data['description'] ?? ''),
                'category' => $data['category'],
            ]);
            $amountInBRL = $expense->applyDiscount((float) $data['discount']);
        }

        $occurredAt = isset($data['occurred_at'])
            ? Carbon::parse($data['occurred_at'])
            : Carbon::now();
        
        $transactionId = $this->transactionRepository->saveTransaction(
            userId: (int) $data['user_id'],
            type: $data['type'],
            category: $data['category'],
            description: $data['description'] ?? null,
            amount: $amountInBRL,
            occurredAt: $occurredAt,
        );

        Event::dispatch(new TransactionCreated(
                transactionId: $transactionId,
                userId: (int) $data['user_id'],
                type: $data['type'],
                amount: $amountInBRL,
                category: $data['category'],
            ));

        if ($data['type'] === 'expense' && $amountInBRL > $this->highExpenseThreshold) {
             $expense = $this->transactionFactory->createTransaction([
                'type' => 'expense',
                'amount' => $amountInBRL,
                'description' => ($data['description'] ?? ''),
                'category' => $data['category'],
            ]);

            Event::dispatch(new HighExpenseDetected(
                userId: (int) $data['user_id'],
                transaction: $expense,
                threshold: $this->highExpenseThreshold,
            ));
        }
        return [
            'id' => $transactionId,
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => number_format($amountInBRL, 2, '.', ''),
            'occurred_at' => $occurredAt->toDateString(),
         ];
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
