<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TransactionCreated;
use App\Events\HighExpenseDetected;
use App\Events\UserRegistered;
use App\Listeners\TransactionCreatedListener;
use App\Listeners\LogHighExpense;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\LogUserRegistered;
use App\Events\NegativeBalanceDetected;
use App\Listeners\LogNegativeBalance;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TransactionCreated::class => [TransactionCreatedListener::class,],
        HighExpenseDetected::class => [LogHighExpense::class,],
        UserRegistered::class => [           
            SendWelcomeEmail::class,
            LogUserRegistered::class,
        ],
        NegativeBalanceDetected::class => [
            LogNegativeBalance::class,
        ]
    ];
}