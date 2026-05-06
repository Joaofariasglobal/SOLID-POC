<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function createUser(array $data) : array
    {
        $existing = DB::table('users')->where('email', $data['email'])->first();
        if ($existing) {
            throw new InvalidArgumentException('E-mail já cadastrado.');
        }

        $id = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("[FinanceService] Usuário criado: id={$id} email={$data['email']}");

        try {
            Mail::raw("Bem-vindo(a), {$data['name']}!", function ($m) use ($data) {
                $m->to($data['email'])->subject('Cadastro realizado');
            });
        } catch (\Throwable $e) {
            Log::warning("[FinanceService] Falha ao enviar e-mail: {$e->getMessage()}");
        }

        return [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
        ];
    }

    public function findUser(int $userId): ?array
    {
        $user = User::findUser($userId);
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}