<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\StatementRepositoryInterface;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;


class EloquentStatementRepository implements StatementRepositoryInterface
{
    public function getStatement(int $userId): array
    {
    $user = User::find($userId);
        if (! $user) {
            throw new InvalidArgumentException('Usuário não encontrado.');
        }

        $rows = DB::table('transactions')->where('user_id', $userId)->get();
    
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'transactions' => $rows->toArray(),
        ];
    }
}