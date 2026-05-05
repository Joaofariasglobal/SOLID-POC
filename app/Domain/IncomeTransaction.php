<?php

namespace App\Domain;

class IncomeTransaction extends BaseTransaction
{
    public function getSignedAmount(): float
    {
        return $this->amount;
    }
}
