<?php

namespace App\Contracts;

interface StatementServiceInterface
{
    public function getStatement(int $userId): array;
}