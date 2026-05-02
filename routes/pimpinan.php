<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Pimpinan\DashboardController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('pimpinan')->name('pimpinan.')->middleware(['auth', 'role:pimpinan'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/export/csv', [DashboardController::class, 'exportCsv'])->name('export.csv');

    // Log Aktivitas
    Route::get('/log',            [ActivityLogController::class, 'showPimpinanLog'])->name('log');
    Route::get('/log/export-csv', [ActivityLogController::class, 'exportPimpinanCsv'])->name('log.export');

    // Profil
    Route::get('/profil',                [UserProfileController::class, 'show'])->name('profile');
    Route::post('/profil/ubah-password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
});
