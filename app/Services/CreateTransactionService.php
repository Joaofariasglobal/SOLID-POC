<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Domain\BaseTransaction;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CreateTransactionService
{
    const CATEGORIAS_RECEITA = ['salario', 'freelance', 'investimento', 'outros'];
    const CATEGORIAS_DESPESA = ['alimentacao', 'transporte', 'moradia', 'lazer', 'saude', 'educacao', 'outros'];
    const COTACAO_USD = 5.20;
    const COTACAO_EUR = 5.65;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) 
    {
    }

    public function saveTransaction(array $data): array
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
        if ($data['type'] === 'income') {
            if (! in_array($data['category'], self::CATEGORIAS_RECEITA, true)) {
                throw new InvalidArgumentException('Categoria inválida para receita.');
            }
        } elseif ($data['type'] === 'expense') {
            if (! in_array($data['category'], self::CATEGORIAS_DESPESA, true)) {
                throw new InvalidArgumentException('Categoria inválida para despesa.');
            }
        }
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'BRL';
        if ($currency === 'USD') {
            $amount = $amount * self::COTACAO_USD;
            $data['amount'] = $amount;  
        } elseif ($currency === 'EUR') {
            $amount = $amount * self::COTACAO_EUR;
            $data['amount'] = $amount;  
        } elseif ($currency !== 'BRL') {
            throw new InvalidArgumentException('Moeda não suportada.');
        }

        $result = $this->transactionRepository->saveTransaction($data);

        Log::info("[FinanceService] Transação salva: id={$result['id']} user={$data['user_id']} type={$data['type']} amount={$amount}");

        if ($data['type'] === 'expense' && $data['amount'] > 5000) 
        {
            Log::warning("[FinanceService] ALERTA: despesa alta detectada para user={$data['user_id']}: R$ {$amount}");
        }
        
        return $result;
    }
}

