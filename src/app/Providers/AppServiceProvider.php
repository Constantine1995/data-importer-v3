<?php

namespace App\Providers;

use App\Services\ApiService;
use App\Services\Sync\IncomesSyncService;
use App\Services\Sync\OrdersSyncService;
use App\Services\Sync\SalesSyncService;
use App\Services\Sync\StocksSyncService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SalesSyncService::class, function ($app) {
            return new SalesSyncService($app->make(ApiService::class));
        });

        $this->app->bind(OrdersSyncService::class, function ($app) {
            return new OrdersSyncService($app->make(ApiService::class));
        });

        $this->app->bind(StocksSyncService::class, function ($app) {
            return new StocksSyncService($app->make(ApiService::class));
        });

        $this->app->bind(IncomesSyncService::class, function ($app) {
            return new IncomesSyncService($app->make(ApiService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
