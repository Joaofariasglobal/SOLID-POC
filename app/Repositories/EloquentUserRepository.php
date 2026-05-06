<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use InvalidArgumentException;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function createUser(array $data): array
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new InvalidArgumentException('E-mail já cadastrado.');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    public function findUser(int $id): ?array
    {
        $user = User::find($id);
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