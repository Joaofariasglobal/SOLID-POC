<?php

namespace App\Contracts;

interface StatementRepositoryInterface
{
    public function getStatement(int $userId): array;
}