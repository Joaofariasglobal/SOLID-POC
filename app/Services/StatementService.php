<?php

namespace App\Services;
use App\Contracts\StatementRepositoryInterface;

class StatementService implements StatementRepositoryInterface
{
    public function __construct(private StatementRepositoryInterface $repository) {}
    public function getStatement(int $userId): array {
        return $this->repository->getStatement($userId);
    }   
}