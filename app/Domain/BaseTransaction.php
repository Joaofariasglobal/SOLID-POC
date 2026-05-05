<?php

namespace App\Domain;

abstract class BaseTransaction
{
    public function __construct(
        public readonly float $amount,
        public readonly string $description,
        public readonly string $category,
    ) {
    }

    public function getSignedAmount(): float
    {
        return $this->amount;
    }

    public function applyDiscount(float $percent): float
    {
        return $this->amount * (1 - $percent / 100);
    }
}
