<?php

namespace App\Providers;

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

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StatementRepositoryInterface::class, EloquentStatementRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(StatementServiceInterface::class, StatementService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
