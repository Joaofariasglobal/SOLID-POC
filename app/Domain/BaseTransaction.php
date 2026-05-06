<?php

namespace App\Domain;

abstract class BaseTransaction
{
    public function __construct(
        public readonly float $amount,
        public readonly string $description,
        public readonly string $category,
    )
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Saldo deve ser maior que 0.');
        }

        if (trim($category) === '') {
            throw new \InvalidArgumentException('Categoria é obrigatória.');
        }
    }

    abstract public function getSignedAmount(): float;
}
