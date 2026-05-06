<?php

namespace App\Contracts;

interface UserServiceInterface
{
    public function createUser(array $data): array;

    public function findUser(int $id): ?array;
}
