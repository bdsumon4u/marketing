<?php

use App\Console\Commands\DistributeSpecialFunds;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(DistributeSpecialFunds::class)
    ->monthly()
    ->at('00:01')
    ->withoutOverlapping()
    ->runInBackground();
