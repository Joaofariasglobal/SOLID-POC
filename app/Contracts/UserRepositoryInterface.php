<?php

namespace App\Contracts;

interface UserRepositoryInterface
{
    public function createUser(array $data): array;
    public function findUser(int $id): ?array;
}