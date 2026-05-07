<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class NegativeBalanceDetected
{
    use Dispatchable;

    public int $userId;
    public float $balance;

    public function __construct(int $userId, float $balance)
    {
        $this->userId = $userId;
        $this->balance = $balance;
    }
}