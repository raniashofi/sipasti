<?php

use App\Http\Controllers\Opd\DashboardController;
use App\Http\Controllers\Opd\DiagnosisMandiriController;
use Illuminate\Support\Facades\Route;

Route::prefix('opd')->name('opd.')->middleware(['auth', 'role:opd'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buat Pengaduan — alur diagnosis mandiri
    Route::prefix('buat-pengaduan')->name('diagnosis.')->group(function () {
        Route::get('/',                         [DiagnosisMandiriController::class, 'index'])->name('index');
        Route::get('/{kategoriId}/mulai',       [DiagnosisMandiriController::class, 'mulai'])->name('mulai');
        Route::get('/node/{nodeId}',            [DiagnosisMandiriController::class, 'showNode'])->name('node');
        Route::get('/tiket',                    [DiagnosisMandiriController::class, 'showTiket'])->name('tiket');
    });

    // Pengaduan Saya
    Route::get('/tiket',      fn() => view('opd.dashboard'))->name('tiket.index');

    // Pusat Bantuan
    Route::get('/bantuan',    fn() => view('opd.dashboard'))->name('bantuan');

    // Profil
    Route::get('/profil',     fn() => view('opd.dashboard'))->name('profile');
});
