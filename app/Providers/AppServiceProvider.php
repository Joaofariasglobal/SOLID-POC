<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EloquentTransactionRepository;
use App\Repositories\EloquentUserRepository;
use App\Contracts\TransactionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(\App\Contracts\TransactionFactoryInterface::class,\App\Domain\Factories\TransactionFactory::class);
        $this->app->bind(\App\Contracts\ExchanceRateProviderInterface::class, fn ($app) => new \App\Services\ConfigExchanceRateProvider(config('exchange.rates', [])));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
