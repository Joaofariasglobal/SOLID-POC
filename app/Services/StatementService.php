<?php

namespace App\Services;

use App\Contracts\StatementServiceInterface;
use App\Contracts\StatementRepositoryInterface;
use App\Events\NegativeBalanceDetected;
use Illuminate\Support\Facades\Event;

class StatementService implements StatementServiceInterface
{
    public function __construct(private StatementRepositoryInterface $repository) {}

    public function getStatement(int $userId): array
    {
        $rawData = $this->repository->getStatement($userId);

        $totalIncome = 0.0;
        $totalExpense = 0.0;
        $byCategory = [];

        foreach ($rawData['transactions'] as $tx) {
            $amount = (float) $tx->amount;

            if ($tx->type === 'income') {
                $totalIncome += $amount;
            } elseif ($tx->type === 'expense') {
                $totalExpense += $amount;
            }

            $key = $tx->type . ':' . ($tx->category ?? 'outros');
            $byCategory[$key] = ($byCategory[$key] ?? 0.0) + $amount;
        }

        $balance = $totalIncome - $totalExpense;
        $status = $balance >= 0 ? 'positivo' : 'negativo';

        if ($balance < 0) {
            Event::dispatch(new NegativeBalanceDetected(
                userId: $userId,
                balance: $balance,
            ));
        }

        return [
            'user' => $rawData['user'],
            'totais' => [
                'receitas' => $totalIncome,
                'despesas' => $totalExpense,
                'saldo' => $balance,
                'status' => $status,
            ],
            'por_categoria' => $byCategory,
            'transacoes' => $rawData['transactions'],
            'gerado_em' => now()->toIso8601String(),
        ];
    }
}
