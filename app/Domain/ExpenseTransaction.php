<?php

namespace App\Domain;

use LogicException;

class ExpenseTransaction extends BaseTransaction
{
    public function getSignedAmount(): float
    {
        return -$this->amount;
    }

    public function applyDiscount(float $percent): float
    {
        throw new LogicException('Não é possível aplicar desconto em uma despesa.');
    }
}
