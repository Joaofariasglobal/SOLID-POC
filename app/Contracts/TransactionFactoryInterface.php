<?php

namespace App\Contracts;

use App\Domain\BaseTransaction;

interface TransactionFactoryInterface
{
    public function fromArray(array $row): BaseTransaction;
}
