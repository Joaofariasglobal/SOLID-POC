<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use Illuminate\Support\Facades\Log;

class TransactionCreatedListener
{
    public function handle(TransactionCreated $event) : void
    {
        Log::info(
            "[TransactionRepository] Transação salva " . 
            "id={$event->transactionId} " .
            "user={$event->userId} " . 
            "type={$event->type} " .
            "amount={$event->amount}"  
        );
    }
}