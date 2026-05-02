<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    DB::table('notifications')
        ->whereNotNull('read_at') // Syarat 1: Hanya yang sudah dibaca
        ->where('created_at', '<', now()->subDays(30)) // Syarat 2: Usianya lebih dari 30 hari
        ->delete(); // Eksekusi hapus
})->dailyAt('00:00');
