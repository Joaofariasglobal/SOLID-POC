<?php

namespace App\Domain;

class IncomeTransaction extends BaseTransaction
{
    public function getSignedAmount(): float
    {
        return $this->amount;
    }
    public function getIcon(): string { return '+'; }
    public function getColor(): string { return 'green'; }
}
