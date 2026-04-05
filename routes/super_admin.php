<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super_admin.')->middleware(['auth', 'role:super_admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Pengguna
    Route::prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/opd',      fn() => view('super_admin.dashboard'))->name('opd');
        Route::get('/internal', fn() => view('super_admin.dashboard'))->name('internal');
    });

    // Konfigurasi Sistem (diagnosis tree & kategori)
    Route::prefix('konfigurasi')->name('konfigurasi.')->group(function () {
        Route::get('/kategori',  fn() => view('super_admin.dashboard'))->name('kategori');
        Route::get('/diagnosis', fn() => view('super_admin.dashboard'))->name('diagnosis');
    });

    // Pustaka Pengetahuan (Knowledge Base)
    Route::prefix('pustaka')->name('pustaka.')->group(function () {
        Route::get('/opd',      fn() => view('super_admin.dashboard'))->name('opd');
        Route::get('/internal', fn() => view('super_admin.dashboard'))->name('internal');
    });

    // Keamanan & Audit
    Route::get('/audit', fn() => view('super_admin.dashboard'))->name('audit');
});
