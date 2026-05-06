<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\StatementRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class EloquentStatementRepository implements StatementRepositoryInterface
{
    public function getStatement(int $userId): array
    {
    $user = User::findUser($userId);
        if (! $user) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }

        $rows = DB::table('transactions')->where('user_id', $userId)->get();

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;
        $porCategoria = [];
        $items = [];

        foreach ($rows as $row) {
            $amount = (float) $row->amount;

            if ($row->type === 'income') {
                $totalReceitas += $amount;
            } elseif ($row->type === 'expense') {
                $totalDespesas += $amount;
            } else {
                throw new RuntimeException("Tipo de transação desconhecido: {$row->type}");
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
                'signed_amount' => $row->type === 'income'
                    ? number_format($amount, 2, '.', '')
                    : number_format(-$amount, 2, '.', ''),
                'occurred_at' => $row->occurred_at,
                'icon' => $row->type === 'income' ? '+' : '-',
                'color' => $row->type === 'income' ? 'green' : 'red',
            ];
        }

        $saldo = $totalReceitas - $totalDespesas;
        $status = $saldo >= 0 ? 'positivo' : 'negativo';

        if ($saldo < 0) {
            Log::warning("[FinanceService] Usuário {$userId} está com saldo negativo: R$ {$saldo}");
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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
            'gerado_em' => now()->toIso8601String(),
        ];
    }
}