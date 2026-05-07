<?php

namespace App\Domain;

class IncomeTransaction extends BaseTransaction
{
    public function getSignedAmount(): float
    {
        return $this->amount;
    }
    public function getType (): string { return 'income'; }
    public function getIcon(): string { return '+'; }
    public function getColor(): string { return 'green'; }
}
