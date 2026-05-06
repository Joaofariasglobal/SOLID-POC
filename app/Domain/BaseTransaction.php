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

    public abstract function getSignedAmount(): float;

    public abstract function getIcon(): string;
    public abstract function getColor(): string;

    public static function fromArray(array $row): self
    {
        if ($row['type'] === 'income') {
            return new IncomeTransaction((float) $row['amount'], $row['description'] ?? '', $row['category'] ?? 'outros');
        } elseif ($row['type'] === 'expense') {
            return new ExpenseTransaction((float) $row['amount'], $row['description'] ?? '', $row['category'] ?? 'outros');
        }
        throw new \RuntimeException("Tipo desconhecido: {$row['type']}");
    }
}
