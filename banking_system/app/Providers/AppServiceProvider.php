<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WithdrawInfo\WithdrawService;
use App\Services\WithdrawInfo\WithdrawServiceDetails;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WithdrawService::class, function () {
            return new WithdrawServiceDetails();
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
