<?php

namespace App\Services;
use App\Contracts\StatementServiceInterface;

class StatementService implements StatementServiceInterface
{
    public function __construct(private StatementServiceInterface $statementService)
    {
           // The line below is not needed as the property is already initialized via constructor property promotion
    }

    public function getStatement(int $userId): array
    {
        return $this->statementService->getStatement($userId);
    }
}