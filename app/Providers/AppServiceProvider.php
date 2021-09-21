<?php

namespace App\Providers;

use App\Services\Adapters\DatabaseStockCheckAdapter;
use App\Services\Adapters\ErpStockCheckAdapter;
use App\Services\Contracts\StockCheckerInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StockCheckerInterface::class, function ($app) {
            switch ($app->make('config')->get('services.stock-checker')) {
                case 'database':
                    return new DatabaseStockCheckAdapter;
                case 'erp':
                    return new ErpStockCheckAdapter;
                default:
                    throw new \RuntimeException("Unknown Stock Checker Service");
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
