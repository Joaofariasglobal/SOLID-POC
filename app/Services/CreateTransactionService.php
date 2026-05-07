<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Domain\CategoryPolicy;
use App\Events\HighExpenseDetected;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CreateTransactionService
{
    private const HIGH_EXPENSE_THRESHOLD = 5000.0;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private ExchangeRateProviderInterface $exchangeRate,
        private CategoryPolicy $categoryPolicy,
        private Dispatcher $events,
        private LoggerInterface $logger,
    ) 
    {
    }

    public function saveTransaction(array $data): array
    {
        $this->validate($data);

        $currency = $data['currency'] ?? 'BRL';
        $data['amount'] = $this->exchangeRate->convertToBRL((float) $data['amount'], $currency);

        $result = $this->transactionRepository->saveTransaction($data);

        $this->logger->info("[Transactions] Transação salva: id={$result['id']} user={$data['user_id']} type={$data['type']} amount={$data['amount']}");

        if ($data['type'] === 'expense' && $data['amount'] > self::HIGH_EXPENSE_THRESHOLD)
        {
            $this->events->dispatch(new HighExpenseDetected((int)$data['user_id'], (float)$data['amount']));
        }

        return $result;
    }

    private function validate(array $data): void
    {
        if (! isset($data['user_id'])) {
            throw new InvalidArgumentException('user_id é obrigatório.');
        }
        if (! $this->userRepository->findUser($data['user_id'])) {
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
        if (! $this->categoryPolicy->isAllowed($data['type'], $data['category'])) {
            throw new InvalidArgumentException("Categoria inválida para {$data['type']}.");
        } 
    }
}

