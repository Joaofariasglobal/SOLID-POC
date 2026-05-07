<?php

namespace App\Contracts;

use App\Domain\BaseTransaction;

interface TransactionFactoryInterface
{
    public function createTransaction(array $data): BaseTransaction;
}
