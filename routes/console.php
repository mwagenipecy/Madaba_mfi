<?php

use App\Jobs\SyncLoanArrearsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SyncLoanArrearsJob())
    ->dailyAt('00:01')
    ->name('sync-loan-arrears')
    ->withoutOverlapping()
    ->onOneServer();
