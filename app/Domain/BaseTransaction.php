<?php

namespace App\Domain;

abstract class BaseTransaction
{
    public function __construct(
        public readonly float $amount,
        public readonly string $description,
        public readonly string $category,
        public readonly ?int $id = null,
        public readonly ?string $occurredAt = null,
    ) {
    }

    abstract public function getType(): string;
    abstract public function getSignedAmount(): float;
    abstract public function getIcon(): string;
    abstract public function getColor(): string;    
}
