<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure polymorphic morph map
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'ExpenseRequest' => \App\Models\ExpenseRequest::class,
            'FundTransfer' => \App\Models\FundTransfer::class,
            'AccountRecharge' => \App\Models\AccountRecharge::class,
            'Loan' => \App\Models\Loan::class,
        ]);
    }
}
