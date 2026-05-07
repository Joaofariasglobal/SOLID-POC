<?php

namespace App\Services;

use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Presenters\TransactionPresenter;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class StatementService
{
    public function __construct(private UserRepositoryInterface $userRepository, private TransactionRepositoryInterface $transactionRepository, private TransactionPresenter $presenter, private LoggerInterface $logger) {}

    public function getStatement(int $userId): array
    {
        $user = $this->userRepository->findUser($userId);
        if (! $user) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }

        $transactions = $this->transactionRepository->findByUser($userId);

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        $porCategoria = [];
        $items = [];

        foreach ($transactions as $tx) {
            $signed = $tx->getSignedAmount();

            if ($signed >= 0) {
                $totalReceitas += $tx->amount;
            } else{
                $totalDespesas += $tx->amount;
            }

            $key = $tx->getType() . ':' . ($tx->category ?: 'outros');
            $porCategoria[$key] = ($porCategoria[$key] ?? 0.0) + $tx->amount;

            $items[] = $this->presenter->toArray($tx);
        }
        
        $saldo = $totalReceitas - $totalDespesas;
        $status = $saldo >= 0 ? 'positivo' : 'negativo';

        if ($saldo < 0) {
            $this->logger->warning("[Statement] Usuário {$userId} está com saldo negativo: R$ {$saldo}");
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