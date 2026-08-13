<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Analytics command
Artisan::command('analytics:aggregate-daily-sales', function () {
    $this->call(\App\Console\Commands\AggregateDailySalesCommand::class);
})->purpose('Aggregate daily product sales from delivered orders');

// Schedule analytics aggregation daily at 00:00
Schedule::command('analytics:aggregate-daily-sales')->dailyAt('00:00');
