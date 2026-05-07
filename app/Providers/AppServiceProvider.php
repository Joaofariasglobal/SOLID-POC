<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProviderInterface;
use App\Services\Currency\StaticRateExchangeRateProvider;
use Illuminate\Support\ServiceProvider;
use App\Contracts\StatementRepositoryInterface;
use App\Repositories\EloquentStatementRepository;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use App\Contracts\TransactionRepositoryInterface;
use App\Repositories\EloquentTransactionRepository;
use App\Services\StatementService;
use App\Contracts\StatementServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Services\UserService;
use App\Contracts\TransactionServiceInterface;
use App\Services\TransactionService;
use App\Contracts\TransactionFactoryInterface;
use App\Domain\TransactionFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExchangeRateProviderInterface::class, StaticRateExchangeRateProvider::class);
        $this->app->bind(StatementRepositoryInterface::class, EloquentStatementRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(StatementServiceInterface::class, StatementService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TransactionServiceInterface::class, function ($app) {
            return new TransactionService(
                $app->make(TransactionFactoryInterface::class),
                $app->make(ExchangeRateProviderInterface::class),
                $app->make(TransactionRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class),
                (float) config('finance.high_expense_threshold', 5000.00),
            );
        });
        $this->app->bind(TransactionFactoryInterface::class, TransactionFactory::class);
    }
}
