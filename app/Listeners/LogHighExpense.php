<?php

namespace App\Listeners;

use App\Events\HighExpenseDetected;
use Psr\Log\LoggerInterface;

class LogHighExpense
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(HighExpenseDetected $event): void
    {
        $this->logger->warning("[Transactions] Despesa alta detectada para o usuário {$event->userId}: R$ {$event->amount}");
    }
}