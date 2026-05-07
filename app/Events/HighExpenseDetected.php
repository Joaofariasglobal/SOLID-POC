<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use App\Domain\ExpenseTransaction;

class HighExpenseDetected
{
    use Dispatchable;
    public int $userId;
    public ExpenseTransaction $transaction;
    public float $threshold;

    public function __construct(int $userId, ExpenseTransaction $transaction, float $threshold)
    {
        $this->userId = $userId;
        $this->transaction = $transaction;
        $this->threshold = $threshold;
    }
}