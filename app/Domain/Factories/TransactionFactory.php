<?php

namespace App\Domain\Factories;

use App\Contracts\TransactionFactoryInterface;
use App\Domain\BaseTransaction;
use App\Domain\ExpenseTransaction;
use App\Domain\IncomeTransaction;
use RuntimeException;

class TransactionFactory implements TransactionFactoryInterface
{
    /** @var array<string, callable(array):BaseTransaction> */
    private array $builders;

    public function __construct()
    {
        $this->builders = [
            'income' => fn (array $r) => new IncomeTransaction(
                (float) $r['amount'],
                $r['description'] ?? '',
                $r['category'] ?? 'outros',
                isset($r['id']) ? (int) $r['id'] : null,
                isset($r['ocurred_at']) ? (string) $r['occurred_at'] : null,
            ),
            'expense' => fn (array $r) => new ExpenseTransaction(
                (float) $r['amount'],
                $r['description'] ?? '',
                $r['category'] ?? 'outros',
            ),
        ];
    }

    public function fromArray(array $row): BaseTransaction
    {
        $type = $row['type'] ?? null;
        if (! isset($this->builders[$type])) {
            throw new RuntimeException("Tipo desconhecido: {$type}");
        }
        return ($this->builders[$type])($row);
    }
}
