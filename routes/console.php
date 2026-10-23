<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('badges:sync-automation')->dailyAt('03:15')->withoutOverlapping();
Schedule::command('messages:prune-large-attachments')->dailyAt('03:35')->withoutOverlapping();
