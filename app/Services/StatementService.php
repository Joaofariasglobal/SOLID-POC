<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Domain\BaseTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class StatementService
{
    public function __construct(private UserRepositoryInterface $userRepository, private TransactionRepositoryInterface $transactionRepository) 
    {
    }

    public function getStatement(int $userId): array
    {
        $user = $this->userRepository->findUser($userId);
        if (! $user) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }

        $rows = $this->transactionRepository->findByUser($userId);

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        $porCategoria = [];
        $items = [];

        foreach ($rows as $row) {
            $tx = BaseTransaction::fromArray(((array) $row));
            $amount = (float) $row->amount;

            if ($tx->getSignedAmount() >= 0) {
                $totalReceitas += $amount;
            } else{
                $totalDespesas += $amount;
            }

            $key = $row->type . ':' . ($row->category ?? 'outros');
            if (! isset($porCategoria[$key])) {
                $porCategoria[$key] = 0.0;
            }
            $porCategoria[$key] += $amount;

            $items[] = [
                'id' => $row->id,
                'type' => $row->type,
                'category' => $row->category,
                'description' => $row->description,
                'amount' => number_format($amount, 2, '.', ''),
                'signed_amount' => number_format($tx->getSignedAmount(), 2, '.', ''),
                'occurred_at' => $row->occurred_at,
                'icon' => $tx->getIcon(),
                'color' => $tx->getColor(),
            ];
        }

        $saldo = $totalReceitas - $totalDespesas;
        $status = $saldo >= 0 ? 'positivo' : 'negativo';

        if ($saldo < 0) {
            Log::warning("[FinanceService] Usuário {$userId} está com saldo negativo: R$ {$saldo}");
        }

        return [
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ],
            'totais' => [
                'receitas' => number_format($totalReceitas, 2, '.', ''),
                'despesas' => number_format($totalDespesas, 2, '.', ''),
                'saldo' => number_format($saldo, 2, '.', ''),
                'status' => $status,
            ],
            'por_categoria' => array_map(
                fn ($v) => number_format($v, 2, '.', ''),
                $porCategoria
            ),
            'transacoes' => $items,
            'gerado_em' => Carbon::now()->toIso8601String(),
        ];
    }
}