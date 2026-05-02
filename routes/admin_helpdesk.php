<?php

use App\Http\Controllers\AdminHelpdesk\ChatController;
use App\Http\Controllers\AdminHelpdesk\DashboardController;
use App\Http\Controllers\AdminHelpdesk\ManajemenTiketController;
use App\Http\Controllers\AdminHelpdesk\PustakaController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-helpdesk')->name('admin_helpdesk.')->middleware(['auth', 'role:admin_helpdesk'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Tiket — Menunggu Verifikasi
    Route::prefix('tiket')->name('tiket.')->group(function () {
        Route::get('/menunggu-verif',           [ManajemenTiketController::class, 'menungguVerif'])->name('menunggu');
        Route::get('/menunggu-verif/export-csv',[ManajemenTiketController::class, 'exportCsv'])->name('menunggu.export');
        Route::get('/panduan-remote',           [ManajemenTiketController::class, 'panduan'])->name('panduan');
        Route::get('/distribusi',               [ManajemenTiketController::class, 'distribusi'])->name('distribusi');
        Route::get('/riwayat',                  [ManajemenTiketController::class, 'riwayat'])->name('riwayat');
        Route::post('/{id}/terima',             [ManajemenTiketController::class, 'terimaProses'])->name('terima');
        Route::post('/{id}/revisi',             [ManajemenTiketController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/transfer',           [ManajemenTiketController::class, 'transfer'])->name('transfer');
        Route::post('/{id}/eskalasi',           [ManajemenTiketController::class, 'eskalasi'])->name('eskalasi');
        Route::post('/{id}/selesai',            [ManajemenTiketController::class, 'selesaiOlehAdmin'])->name('selesai');
        Route::get('/{id}/chat',                [ChatController::class, 'show'])->name('chat');
        Route::post('/{id}/chat/send',          [ChatController::class, 'send'])->name('chat.send');
    });

    // Pustaka Solusi (read-only, internal, sesuai bidang)
    Route::get('/pustaka',        [PustakaController::class, 'index'])->name('pustaka');
    Route::get('/pustaka/{id}',   [PustakaController::class, 'show'])->name('pustaka.show');

    // Log Aktivitas (hanya role admin_helpdesk)
    Route::get('/log',            [ActivityLogController::class, 'showAdminLog'])->name('log');
    Route::get('/log/export-csv', [ActivityLogController::class, 'exportAdminCsv'])->name('log.export');

    // Profil
    Route::get('/profil',                [UserProfileController::class, 'show'])->name('profile');
    Route::post('/profil/ubah-password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
});
