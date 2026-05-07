<?php

namespace App\Events;

class HighExpenseDetected
{
    public function __construct(public readonly int $userId, public readonly float $amount)
    {
    }
}