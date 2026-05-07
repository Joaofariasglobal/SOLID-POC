<?php

namespace App\Listeners;

use App\Events\HighExpenseDetected;
use Illuminate\Support\Facades\Log;

class LogHighExpense{
    public function handle(HighExpenseDetected $event){
        Log::warning("Despesa alta para o usuário: {$event->userId}", [
            'transaction' => $event->transaction,
            'threshold' => $event->threshold
        ]);
    }
}