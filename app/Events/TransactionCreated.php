<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TransactionCreated
{
    use Dispatchable;

    public int $transactionId;
    public int $userId;
    public string $type;
    public float $amount;
    public string $category;

    public function __construct(int $transactionId, int $userId, string $type, float $amount, string $category)
    {
        $this->transactionId = $transactionId;
        $this->userId = $userId;
        $this->type = $type;
        $this->amount = $amount;
        $this->category = $category;
    }
}