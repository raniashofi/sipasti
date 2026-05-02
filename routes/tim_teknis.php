<?php

use App\Http\Controllers\TimTeknis\AntreanController;
use App\Http\Controllers\TimTeknis\ChatController;
use App\Http\Controllers\TimTeknis\DashboardController;
use App\Http\Controllers\TimTeknis\PustakaController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('tim-teknis')->name('tim_teknis.')->middleware(['auth', 'role:tim_teknis'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Antrean Tugas
    Route::get('/antrean', [AntreanController::class, 'index'])->name('antrean');
    Route::get('/riwayat', [AntreanController::class, 'riwayat'])->name('riwayat');

    // Tiket Actions
    Route::prefix('tiket')->name('tiket.')->group(function () {
        Route::post('{id}/selesai', [AntreanController::class, 'selesai'])->name('selesai');
        Route::post('{id}/gagal', [AntreanController::class, 'gagal'])->name('gagal');
        Route::post('{id}/kembalikan', [AntreanController::class, 'kembalikan'])->name('kembalikan');
        Route::get('{id}/chat', [ChatController::class, 'show'])->name('chat');
        Route::post('{id}/chat/send', [ChatController::class, 'send'])->name('chat.send');
    });

    // Pustaka Teknis (SOP)
    Route::get('/pustaka', [PustakaController::class, 'index'])->name('pustaka');
    Route::get('/pustaka/{id}', [PustakaController::class, 'show'])->name('pustaka.show');

    // Profil
    Route::get('/profil',                [UserProfileController::class, 'show'])->name('profile');
    Route::post('/profil/ubah-password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/ubah-status',   [UserProfileController::class, 'updateStatus'])->name('profile.status');
});
