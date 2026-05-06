<?php

namespace App\Domain;

class ExpenseTransaction extends BaseTransaction
{
    public function getSignedAmount(): float
    {
        return -$this->amount;
    }

    public function applyDiscount(float $percent): float
    {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException('Percentual de desconto deve estar entre 0 e 100.');
        }

        return $this->amount * (1 - $percent / 100);
    }
}
