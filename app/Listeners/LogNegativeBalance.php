<?php

namespace App\Listeners;

use App\Events\NegativeBalanceDetected;
use Illuminate\Support\Facades\Log;

class LogNegativeBalance 
{
    public function handle(NegativeBalanceDetected $event) : void
    {
        Log::warning(
            "Saldo negativo detectado: {$event->userId}. " .
            "Saldo atual: {$event->balance}"
        );
    }
}