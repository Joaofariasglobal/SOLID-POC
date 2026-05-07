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

        return [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
        ];
    }

    public function findUser(int $userId): ?array
    {
        $user = User::find($userId);
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